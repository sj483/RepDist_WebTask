CREATE TABLE `c01_DataStore`.`Feedback` (
    `SubjectId` TEXT NOT NULL,
    `Feedback` TEXT NULL DEFAULT NULL,
    `DateTime_Feedback` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`SubjectId`(8))
) ENGINE = InnoDB;