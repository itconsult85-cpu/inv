-- Synchronize ngdata when detail_materialmasuk is inserted, updated, or deleted.
-- Run this once on the inventory database. No existing data is deleted.

DELIMITER $$

DROP TRIGGER IF EXISTS `ngdata_insert_materialmasuk`$$
CREATE TRIGGER `ngdata_insert_materialmasuk`
AFTER INSERT ON `detail_materialmasuk`
FOR EACH ROW
BEGIN
    IF NEW.idsup IS NOT NULL AND NEW.detmatkode IS NOT NULL THEN
        UPDATE `ngdata`
        SET `beratmatmasuk` = NEW.detsubtotal,
            `beratng` = `beratmatkeluar` - NEW.detsubtotal
        WHERE `detfaktur` = NEW.detfaktur
          AND `detbrgkode` IS NULL
          AND `tgl` = NEW.tgl
          AND `idsup` = NEW.idsup
          AND `matjenis` = NEW.detmatkode;

        IF ROW_COUNT() = 0 THEN
            INSERT INTO `ngdata`
                (`detfaktur`, `detbrgkode`, `idsup`, `tgl`, `matjenis`, `beratmatkeluar`, `beratmatmasuk`, `beratng`, `satuan`)
            VALUES
                (NEW.detfaktur, NULL, NEW.idsup, NEW.tgl, NEW.detmatkode, 0, NEW.detsubtotal, 0 - NEW.detsubtotal, NEW.satuan);
        END IF;
    END IF;
END$$

DROP TRIGGER IF EXISTS `ngdata_update_materialmasuk`$$
CREATE TRIGGER `ngdata_update_materialmasuk`
AFTER UPDATE ON `detail_materialmasuk`
FOR EACH ROW
BEGIN
    UPDATE `ngdata`
    SET `beratmatmasuk` = NEW.detsubtotal,
        `beratng` = `beratmatkeluar` - NEW.detsubtotal
    WHERE `detfaktur` = NEW.detfaktur
      AND `detbrgkode` IS NULL
      AND `tgl` = NEW.tgl
      AND `idsup` = NEW.idsup
      AND `matjenis` = NEW.detmatkode;
END$$

DROP TRIGGER IF EXISTS `ngdata_delete_materialmasuk`$$
CREATE TRIGGER `ngdata_delete_materialmasuk`
AFTER DELETE ON `detail_materialmasuk`
FOR EACH ROW
BEGIN
    UPDATE `ngdata`
    SET `beratmatmasuk` = 0,
        `beratng` = `beratmatkeluar`
    WHERE `detfaktur` = OLD.detfaktur
      AND `detbrgkode` IS NULL
      AND `tgl` = OLD.tgl
      AND `idsup` = OLD.idsup
      AND `matjenis` = OLD.detmatkode;

    DELETE FROM `ngdata`
    WHERE `detfaktur` = OLD.detfaktur
      AND `detbrgkode` IS NULL
      AND `tgl` = OLD.tgl
      AND `idsup` = OLD.idsup
      AND `matjenis` = OLD.detmatkode
      AND `beratmatkeluar` = 0
      AND `beratmatmasuk` = 0;
END$$

DELIMITER ;
