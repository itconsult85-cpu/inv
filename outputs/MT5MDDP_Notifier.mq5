#property strict
#property version   "1.10"
#property description "MT5MDDP close-trade Telegram notifier. It does not open or modify trades."

input string InpBotToken = "";       // Paste the Telegram bot token here inside MT5
input string InpChatID   = "";       // Private Telegram chat ID
input bool   InpSendTestOnStart = true;
input string InpOnlySymbol = "";     // Blank = all symbols; e.g. XAUUSD.vx = only that exact symbol

struct CloseRecord
{
   long   position_id;
   double volume;
   double deal_net;
};

string JsonEscape(string value)
{
   StringReplace(value, "\\", "\\\\");
   StringReplace(value, "\"", "\\\"");
   StringReplace(value, "\r", "\\r");
   StringReplace(value, "\n", "\\n");
   return value;
}

bool SendTelegram(const string message)
{
   if(InpBotToken == "" || InpChatID == "")
   {
      Print("MT5MDDP: isi Bot Token dan Chat ID pada Inputs EA.");
      return false;
   }

   string url = "https://api.telegram.org/bot" + InpBotToken + "/sendMessage";
   string body = "{\"chat_id\":\"" + JsonEscape(InpChatID)
                 + "\",\"text\":\"" + JsonEscape(message) + "\"}";

   char data[];
   int size = StringToCharArray(body, data, 0, WHOLE_ARRAY, CP_UTF8);
   if(size <= 1)
   {
      Print("MT5MDDP: gagal menyiapkan pesan.");
      return false;
   }
   ArrayResize(data, size - 1);

   char response[];
   string response_headers;
   string headers = "Content-Type: application/json\r\n";

   ResetLastError();
   int status = WebRequest("POST", url, headers, 10000, data, response, response_headers);
   string response_text = CharArrayToString(response, 0, -1, CP_UTF8);

   if(status != 200 || StringFind(response_text, "\"ok\":true") < 0)
   {
      Print("MT5MDDP: Telegram send failed. HTTP=", status,
            ", MT5 error=", GetLastError(), ", response=", response_text);
      return false;
   }

   Print("MT5MDDP: Telegram message sent.");
   return true;
}

string ExecutionType(const string symbol)
{
   long mode = SymbolInfoInteger(symbol, SYMBOL_TRADE_EXEMODE);
   if(mode == SYMBOL_TRADE_EXECUTION_INSTANT)  return "Instant";
   if(mode == SYMBOL_TRADE_EXECUTION_MARKET)   return "Market";
   if(mode == SYMBOL_TRADE_EXECUTION_REQUEST)  return "Request";
   if(mode == SYMBOL_TRADE_EXECUTION_EXCHANGE) return "Exchange";
   return "Market";
}

string SignedValue(const double value, const int decimals)
{
   string prefix = (value > 0.0 ? "+" : "");
   return prefix + DoubleToString(value, decimals);
}

string MoneyValue(const double value, const bool show_plus)
{
   string prefix = "";
   if(value < 0.0)
      prefix = "-";
   else if(show_plus && value > 0.0)
      prefix = "+";

   return prefix + DoubleToString(MathAbs(value), 2) + " "
          + AccountInfoString(ACCOUNT_CURRENCY);
}

string DateTimeDMY(const datetime value)
{
   MqlDateTime dt;
   TimeToStruct(value, dt);
   return StringFormat("%02d-%02d-%04d %02d:%02d",
                       dt.day, dt.mon, dt.year, dt.hour, dt.min);
}

// Returns the average opening price, opening volume, opening side,
// opening commissions/fees, and the SL/TP recorded on opening orders.
bool GetPositionDetails(const long position_id,
                        double &entry_price,
                        double &opened_volume,
                        string &side,
                        double &opening_costs,
                        double &stop_loss,
                        double &take_profit)
{
   entry_price = 0.0;
   opened_volume = 0.0;
   side = "Unknown";
   opening_costs = 0.0;
   stop_loss = 0.0;
   take_profit = 0.0;
   double weighted_price = 0.0;

   if(!HistorySelectByPosition(position_id))
      return false;

   int total = HistoryDealsTotal();
   for(int i = 0; i < total; i++)
   {
      ulong ticket = HistoryDealGetTicket(i);
      if(ticket == 0)
         continue;

      long entry = HistoryDealGetInteger(ticket, DEAL_ENTRY);
      if(entry != DEAL_ENTRY_IN)
         continue;

      long type = HistoryDealGetInteger(ticket, DEAL_TYPE);
      if(type != DEAL_TYPE_BUY && type != DEAL_TYPE_SELL)
         continue;

      double volume = HistoryDealGetDouble(ticket, DEAL_VOLUME);
      double price = HistoryDealGetDouble(ticket, DEAL_PRICE);
      weighted_price += volume * price;
      opened_volume += volume;
      opening_costs += HistoryDealGetDouble(ticket, DEAL_COMMISSION)
                       + HistoryDealGetDouble(ticket, DEAL_FEE);

      if(side == "Unknown")
         side = (type == DEAL_TYPE_BUY ? "Buy" : "Sell");

      ulong order_ticket = (ulong)HistoryDealGetInteger(ticket, DEAL_ORDER);
      if(order_ticket > 0 && HistoryOrderSelect(order_ticket))
      {
         double order_sl = HistoryOrderGetDouble(order_ticket, ORDER_SL);
         double order_tp = HistoryOrderGetDouble(order_ticket, ORDER_TP);
         if(order_sl != 0.0 || order_tp != 0.0)
         {
            stop_loss = order_sl;
            take_profit = order_tp;
         }
      }
   }

   if(opened_volume > 0.0)
      entry_price = weighted_price / opened_volume;

   return (opened_volume > 0.0);
}

bool IsForexPair(const string symbol)
{
   if(StringLen(symbol) < 6)
      return false;

   string upper_symbol = symbol;
   StringToUpper(upper_symbol);
   string base_currency = StringSubstr(upper_symbol, 0, 3);
   string quote_currency = StringSubstr(upper_symbol, 3, 3);
   string currency_codes = "|USD|EUR|GBP|JPY|CHF|CAD|AUD|NZD|CNH|CNY|SGD|HKD|NOK|SEK|DKK|PLN|MXN|ZAR|TRY|HUF|CZK|THB|ILS|BGN|RON|";

   return (StringFind(currency_codes, "|" + base_currency + "|") >= 0
           && StringFind(currency_codes, "|" + quote_currency + "|") >= 0);
}

double PipSizeForSymbol(const string symbol)
{
   string upper_symbol = symbol;
   StringToUpper(upper_symbol);

   // This matches the sample's gold convention: 0.10 price movement = 1 pip.
   if(StringFind(upper_symbol, "XAU") >= 0)
      return 0.10;

   // Pips are only reported for gold and recognized FX currency pairs.
   // Crypto/indices/other CFDs have broker-specific point conventions.
   if(!IsForexPair(upper_symbol))
      return 0.0;

   double point = SymbolInfoDouble(symbol, SYMBOL_POINT);
   int digits = (int)SymbolInfoInteger(symbol, SYMBOL_DIGITS);
   if(point <= 0.0)
      return 0.0;

   if(digits == 3 || digits == 5)
      return point * 10.0;
   if(digits == 2 || digits == 4)
      return point;

   return 0.0;
}

datetime PeriodStartToday()
{
   MqlDateTime dt;
   TimeToStruct(TimeCurrent(), dt);
   dt.hour = 0;
   dt.min = 0;
   dt.sec = 0;
   return StructToTime(dt);
}

datetime PeriodStartWeek()
{
   MqlDateTime dt;
   TimeToStruct(TimeCurrent(), dt);
   int days_from_monday = (dt.day_of_week + 6) % 7;
   dt.hour = 0;
   dt.min = 0;
   dt.sec = 0;
   datetime midnight = StructToTime(dt);
   return midnight - (days_from_monday * 86400);
}

datetime PeriodStartMonth()
{
   MqlDateTime dt;
   TimeToStruct(TimeCurrent(), dt);
   dt.day = 1;
   dt.hour = 0;
   dt.min = 0;
   dt.sec = 0;
   return StructToTime(dt);
}

double ClosedNetForPeriod(const string symbol, const datetime from_time)
{
   datetime to_time = TimeCurrent();
   if(!HistorySelect(from_time, to_time))
      return 0.0;

   CloseRecord records[];
   int count = 0;
   int total = HistoryDealsTotal();

   // Copy closing-deal data before selecting each full position history below.
   for(int i = 0; i < total; i++)
   {
      ulong ticket = HistoryDealGetTicket(i);
      if(ticket == 0)
         continue;

      if(HistoryDealGetString(ticket, DEAL_SYMBOL) != symbol)
         continue;

      long entry = HistoryDealGetInteger(ticket, DEAL_ENTRY);
      if(entry != DEAL_ENTRY_OUT && entry != DEAL_ENTRY_OUT_BY)
         continue;

      CloseRecord row;
      row.position_id = HistoryDealGetInteger(ticket, DEAL_POSITION_ID);
      row.volume = HistoryDealGetDouble(ticket, DEAL_VOLUME);
      row.deal_net = HistoryDealGetDouble(ticket, DEAL_PROFIT)
                     + HistoryDealGetDouble(ticket, DEAL_SWAP)
                     + HistoryDealGetDouble(ticket, DEAL_COMMISSION)
                     + HistoryDealGetDouble(ticket, DEAL_FEE);

      ArrayResize(records, count + 1);
      records[count] = row;
      count++;
   }

   double result = 0.0;
   for(int i = 0; i < count; i++)
   {
      double entry_price, opened_volume, opening_costs, sl, tp;
      string side;
      GetPositionDetails(records[i].position_id, entry_price, opened_volume,
                         side, opening_costs, sl, tp);

      double allocated_opening_costs = 0.0;
      if(opened_volume > 0.0)
         allocated_opening_costs = opening_costs * records[i].volume / opened_volume;

      result += records[i].deal_net + allocated_opening_costs;
   }

   return result;
}

string ProfitStatus(const double net_result)
{
   if(net_result > 0.0) return "(\x2705 PROFIT)";
   if(net_result < 0.0) return "(\x274C LOSS)";
   return "(BREAKEVEN)";
}

int OnInit()
{
   if(InpBotToken == "" || InpChatID == "")
   {
      Print("MT5MDDP: masukkan Bot Token dan Chat ID di tab Inputs.");
      return INIT_PARAMETERS_INCORRECT;
   }

   if(InpSendTestOnStart)
      SendTelegram("MT5MDDP Scalpers\nTelegram notifier connected to your MT5 account.");

   return INIT_SUCCEEDED;
}

void OnTradeTransaction(const MqlTradeTransaction &trans,
                        const MqlTradeRequest &request,
                        const MqlTradeResult &result)
{
   if(trans.type != TRADE_TRANSACTION_DEAL_ADD || trans.deal == 0)
      return;

   if(!HistoryDealSelect(trans.deal))
      return;

   long entry = HistoryDealGetInteger(trans.deal, DEAL_ENTRY);
   if(entry != DEAL_ENTRY_OUT && entry != DEAL_ENTRY_OUT_BY && entry != DEAL_ENTRY_INOUT)
      return;

   string symbol = HistoryDealGetString(trans.deal, DEAL_SYMBOL);
   if(InpOnlySymbol != "" && symbol != InpOnlySymbol)
      return;

   long deal_type = HistoryDealGetInteger(trans.deal, DEAL_TYPE);
   if(deal_type != DEAL_TYPE_BUY && deal_type != DEAL_TYPE_SELL)
      return;

   double volume = HistoryDealGetDouble(trans.deal, DEAL_VOLUME);
   double close_price = HistoryDealGetDouble(trans.deal, DEAL_PRICE);
   long position_id = HistoryDealGetInteger(trans.deal, DEAL_POSITION_ID);
   datetime deal_time = (datetime)HistoryDealGetInteger(trans.deal, DEAL_TIME);
   ulong deal_ticket = trans.deal;

   double deal_net = HistoryDealGetDouble(trans.deal, DEAL_PROFIT)
                     + HistoryDealGetDouble(trans.deal, DEAL_SWAP)
                     + HistoryDealGetDouble(trans.deal, DEAL_COMMISSION)
                     + HistoryDealGetDouble(trans.deal, DEAL_FEE);

   double entry_price, opened_volume, opening_costs, stop_loss, take_profit;
   string side;
   bool has_entry = GetPositionDetails(position_id, entry_price, opened_volume,
                                       side, opening_costs, stop_loss, take_profit);

   double allocated_opening_costs = 0.0;
   if(has_entry && opened_volume > 0.0)
      allocated_opening_costs = opening_costs * volume / opened_volume;
   double net_result = deal_net + allocated_opening_costs;

   int digits = (int)SymbolInfoInteger(symbol, SYMBOL_DIGITS);
   if(digits < 0 || digits > 8)
      digits = 2;

   double pips = 0.0;
   bool has_pips = (has_entry && PipSizeForSymbol(symbol) > 0.0);
   if(has_pips)
   {
      double favorable_move = (side == "Buy" ? close_price - entry_price
                                               : entry_price - close_price);
      pips = favorable_move / PipSizeForSymbol(symbol);
   }

   string message = "MT5MDDP Scalpers\n\n"
                    + "Type : Close Order " + ProfitStatus(net_result) + "\n"
                    + "ID : " + IntegerToString((long)deal_ticket) + "\n"
                    + "Date/Time : " + DateTimeDMY(deal_time) + "\n"
                    + "Pair : " + symbol + "\n"
                    + "Order Type : " + ExecutionType(symbol) + "\n"
                    + "Order : " + side + "\n"
                    + "Entry Price : " + (has_entry ? DoubleToString(entry_price, digits) : "N/A") + "\n"
                    + "Close Price : " + DoubleToString(close_price, digits) + "\n"
                    + "Lot Size : " + DoubleToString(volume, 2) + "\n"
                    + "Stop Loss : " + DoubleToString(stop_loss, digits) + "\n"
                    + "Take Profit : " + DoubleToString(take_profit, digits) + "\n"
                    + "Profit/Loss : ";

   if(has_pips)
      message += SignedValue(pips, 1) + " Pips (" + MoneyValue(net_result, true) + ")";
   else
      message += MoneyValue(net_result, true);

   double today = ClosedNetForPeriod(symbol, PeriodStartToday());
   double week = ClosedNetForPeriod(symbol, PeriodStartWeek());
   double month = ClosedNetForPeriod(symbol, PeriodStartMonth());

   message += "\n\nSUMMARY PERFORMANCE (" + symbol + ")\n"
              + "Today: " + MoneyValue(today, false) + "\n"
              + "This Week: " + MoneyValue(week, false) + "\n"
              + "This Month: " + MoneyValue(month, false) + "\n\n"
              + "\x26A0 Disclaimer: This content is for educational purposes only. "
              + "Please use proper risk management and trade responsibly.";

   SendTelegram(message);
}
