public function getChartDataPo5()
{
$poMasukModel = new Modeldetailpo();
$yearpo = $this->request->getGet('yearpo') ?? date('Y');
$comparisonDataPo = $poMasukModel->getTop5ProductsPerMonthPo($yearpo);

$datasets = [];
$labels = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

$items = [];

foreach ($comparisonDataPo['currentYearPo'] as $bulan => $products) {
foreach ($products as $product) {
$kode = $product['detkodebrg'];
if (!isset($items[$kode])) {
$items[$kode] = [
'currentYear' => array_fill(0, 12, 0),
'previousYear' => array_fill(0, 12, 0)
];
}
$items[$kode]['currentYear'][$bulan - 1] = $product['total'];
}
}

foreach ($comparisonDataPo['previousYearPo'] as $bulan => $products) {
foreach ($products as $product) {
$kode = $product['detkodebrg'];
if (!isset($items[$kode])) {
$items[$kode] = [
'currentYear' => array_fill(0, 12, 0),
'previousYear' => array_fill(0, 12, 0)
];
}
$items[$kode]['previousYear'][$bulan - 1] = $product['total'];
}
}

foreach ($items as $kode => $data) {
$datasets[] = [
'label' => $kode . ' ' . $yearpo,
'backgroundColor' => '#36A2EB',
'borderColor' => '#36A2EB',
'borderWidth' => 1,
'data' => $data['currentYear']
];
$datasets[] = [
'label' => $kode . ' ' . ($yearpo - 1),
'backgroundColor' => '#FF0000',
'borderColor' => '#FF0000',
'borderWidth' => 1,
'data' => $data['previousYear']
];
}

$response = [
'labels' => $labels,
'datasets' => $datasets,
'yearpo' => $yearpo
];

return $this->response->setJSON($response);
}


public function getChartDataPo5()
{
$poMasukModel = new Modeldetailpo();
$yearpo = $this->request->getGet('yearpo') ?? date('Y');
$comparisonDataPo = $poMasukModel->getTop5ProductsPerMonthPo($yearpo);

$datasets = [];
$labels = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

// Process current year data
foreach ($comparisonDataPo['currentYearPo'] as $bulan => $products) {
foreach ($products as $product) {
$kode = $product['detkodebrg'];
$currentYearKey = $kode . '_current';
$previousYearKey = $kode . '_previous';

// Initialize current year dataset if not exist
if (!isset($datasets[$currentYearKey])) {
$datasets[$currentYearKey] = [
'label' => $kode . ' ' . $yearpo,
'backgroundColor' => '#36A2EB',
'borderColor' => '#36A2EB',
'borderWidth' => 1,
'data' => array_fill(0, 12, 0)
];
}
$datasets[$currentYearKey]['data'][$bulan - 1] = $product['total'];

// Initialize previous year dataset if not exist
if (!isset($datasets[$previousYearKey])) {
$datasets[$previousYearKey] = [
'label' => $kode . ' ' . ($yearpo - 1),
'backgroundColor' => '#FF0000',
'borderColor' => '#FF0000',
'borderWidth' => 1,
'data' => array_fill(0, 12, 0)
];
}
}
}

// Process previous year data
foreach ($comparisonDataPo['previousYearPo'] as $bulan => $products) {
foreach ($products as $product) {
$kode = $product['detkodebrg'];
$previousYearKey = $kode . '_previous';
if (!isset($datasets[$previousYearKey])) {
$datasets[$previousYearKey] = [
'label' => $kode . ' ' . ($yearpo - 1),
'backgroundColor' => '#FF0000',
'borderColor' => '#FF0000',
'borderWidth' => 1,
'data' => array_fill(0, 12, 0)
];
}
$datasets[$previousYearKey]['data'][$bulan - 1] = $product['total'];
}
}

$response = [
'labels' => $labels,
'datasets' => array_values($datasets),
'yearpo' => $yearpo
];

return $this->response->setJSON($response);
}


<!-- hampir benar -->
public function getChartDataPo5()
{
$poMasukModel = new Modeldetailpo();
$yearpo = $this->request->getGet('yearpo') ?? date('Y');
$comparisonDataPo = $poMasukModel->getTop5ProductsPerMonthPo($yearpo);

$datasets = [];
$labels = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

foreach (range(1, 12) as $bulan) {
$currentTopProducts = array_slice($comparisonDataPo['currentYearPo'][$bulan] ?? [], 0, 5);
$previousTopProducts = array_slice($comparisonDataPo['previousYearPo'][$bulan] ?? [], 0, 5);

foreach ($currentTopProducts as $product) {
$kode = $product['detkodebrg'];
$key = $kode . '_current_' . $bulan;
if (!isset($datasets[$key])) {
$datasets[$key] = [
'label' => $kode . ' ' . $yearpo,
'backgroundColor' => '#36A2EB',
'borderColor' => '#36A2EB',
'borderWidth' => 1,
'data' => array_fill(0, 12, 0)
];
}
$datasets[$key]['data'][$bulan - 1] = $product['total'];
}

foreach ($previousTopProducts as $product) {
$kode = $product['detkodebrg'];
$key = $kode . '_previous_' . $bulan;
if (!isset($datasets[$key])) {
$datasets[$key] = [
'label' => $kode . ' ' . ($yearpo - 1),
'backgroundColor' => '#FF0000',
'borderColor' => '#FF0000',
'borderWidth' => 1,
'data' => array_fill(0, 12, 0)
];
}
$datasets[$key]['data'][$bulan - 1] = $product['total'];
}
}

$finalDatasets = array_values($datasets);

$response = [
'labels' => $labels,
'datasets' => $finalDatasets,
'yearpo' => $yearpo
];

return $this->response->setJSON($response);
}

<!-- sedikit lagi -->
public function getChartDataPo5()
{
$poMasukModel = new Modeldetailpo();
$yearpo = $this->request->getGet('yearpo') ?? date('Y');
$comparisonDataPo = $poMasukModel->getTop5ProductsPerMonthPo($yearpo);

$datasets = [];
$labels = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

foreach (range(1, 12) as $bulan) {
$currentTopProducts = array_slice($comparisonDataPo['currentYearPo'][$bulan] ?? [], 0, 5);
$previousTopProducts = array_slice($comparisonDataPo['previousYearPo'][$bulan] ?? [], 0, 5);

foreach ($currentTopProducts as $product) {
$kode = $product['detkodebrg'];
if (!isset($datasets[$kode])) {
$datasets[$kode] = [
'label' => $kode,
'data' => array_fill(0, 12, 0), // 12 bulan x 2 tahun
'backgroundColor' => '#36A2EB',
'borderColor' => '#36A2EB',
'borderWidth' => 1
];
}
$datasets[$kode]['data'][$bulan - 1] = $product['total'];
}

foreach ($previousTopProducts as $product) {
$kode = $product['detkodebrg'];
if (!isset($datasets[$kode])) {
$datasets[$kode] = [
'label' => $kode,
'data' => array_fill(0, 12, 0), // 12 bulan x 2 tahun
'backgroundColor' => '#FF0000',
'borderColor' => '#FF0000',
'borderWidth' => 1
];
}
$datasets[$kode]['data'][$bulan - 1] = $product['total']; // Offset by 12 for the previous year
}
}

$finalDatasets = array_values($datasets);

$response = [
'labels' => $labels, // Duplicate labels for 2 years
'datasets' => $finalDatasets,
'yearpo' => $yearpo
];

return $this->response->setJSON($response);
}