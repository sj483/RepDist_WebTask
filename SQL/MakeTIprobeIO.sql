CREATE TABLE `c01_DataStore`.`TIprobeIO` (
    `SubjectId` TEXT NOT NULL,
    `DateTime_Write` DATETIME NULL DEFAULT NULL,
    `ClientTimeZone` TEXT NULL DEFAULT NULL,
    `TIprobeIO` TEXT NULL DEFAULT NULL,
    PRIMARY KEY (`SubjectId`(8))
) ENGINE = InnoDB;