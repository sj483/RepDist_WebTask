CREATE TABLE `c01_DataStore`.`InstructNaughtiness` (
    `SubjectId` TEXT NULL DEFAULT NULL,
    `State` INT NULL DEFAULT NULL,
    `TaskId` TEXT NULL DEFAULT NULL,
    `DateTime_Naughty` DATETIME NULL DEFAULT NULL
) ENGINE = InnoDB;