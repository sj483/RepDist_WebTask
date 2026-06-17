-- Combined SQL generated from all files in SQL/
-- Database target updated to learning_c01_DataStore

CREATE DATABASE IF NOT EXISTS `learning_c01_DataStore`;
USE `learning_c01_DataStore`;

-- Source: MakeConsentLog.sql
CREATE TABLE `learning_c01_DataStore`.`ConsentLog` (
    `SubjectId` TEXT NOT NULL,
    `Initials` TEXT NULL DEFAULT NULL,
    `DateTime_Consent` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`SubjectId`(8))
) ENGINE = InnoDB;

-- Source: MakeExclusions.sql
CREATE TABLE `learning_c01_DataStore`.`Exclusions` (
    `PoolId` TEXT NULL DEFAULT NULL,
    `SubjectId` TEXT NULL DEFAULT NULL,
    `OS` TEXT NULL DEFAULT NULL,
    `Browser` TEXT NULL DEFAULT NULL,
    `ScreenWidth` INT UNSIGNED NULL DEFAULT NULL,
    `ScreenHeight` INT UNSIGNED NULL DEFAULT NULL,
    `DateTime_Exclude` DATETIME NULL DEFAULT NULL
) ENGINE = InnoDB;

-- Source: MakeFeedback.sql
CREATE TABLE `learning_c01_DataStore`.`Feedback` (
    `SubjectId` TEXT NOT NULL,
    `Feedback` TEXT NULL DEFAULT NULL,
    `DateTime_Feedback` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`SubjectId`(8))
) ENGINE = InnoDB;

-- Source: MakeInstructNaughtiness.sql
CREATE TABLE `learning_c01_DataStore`.`InstructNaughtiness` (
    `SubjectId` TEXT NULL DEFAULT NULL,
    `State` INT NULL DEFAULT NULL,
    `TaskId` TEXT NULL DEFAULT NULL,
    `DateTime_Naughty` DATETIME NULL DEFAULT NULL
) ENGINE = InnoDB;

-- Source: MakeRegister.sql
CREATE TABLE `learning_c01_DataStore`.`Register` (
    `PoolId` TEXT NULL DEFAULT NULL,
    `SubjectId` TEXT NOT NULL,
    `BMY` CHAR(7) NULL DEFAULT NULL,
    `Gender` TEXT NULL DEFAULT NULL,
    `Handedness` TEXT NULL DEFAULT NULL,
    `L1` TEXT NULL DEFAULT NULL,
    `State` INT NULL DEFAULT NULL,
    `GroupId` TEXT NULL DEFAULT NULL,
    `ImgPerm` TEXT NULL DEFAULT NULL,
    `DateTime_Landing` DATETIME NULL DEFAULT NULL,
    `DateTime_Consent` DATETIME NULL DEFAULT NULL,
    `DateTime_Register` DATETIME NULL DEFAULT NULL,
    `DateTime_TIinstr` DATETIME NULL DEFAULT NULL,
    `DateTime_TItrain` DATETIME NULL DEFAULT NULL,
    `DateTime_TIprobe` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`SubjectId`(8))
) ENGINE = InnoDB;

-- Source: MakeRelandings.sql
CREATE TABLE `learning_c01_DataStore`.`Relandings` (
    `PoolId` TEXT NULL DEFAULT NULL,
    `SubjectId` TEXT NULL DEFAULT NULL,
    `State` INT NULL DEFAULT NULL,
    `DateTime_Reland` DATETIME NULL DEFAULT NULL
) ENGINE = InnoDB;

-- Source: MakeTIprobeIO.sql
CREATE TABLE `learning_c01_DataStore`.`TIprobeIO` (
    `SubjectId` TEXT NOT NULL,
    `DateTime_Write` DATETIME NULL DEFAULT NULL,
    `ClientTimeZone` TEXT NULL DEFAULT NULL,
    `TIprobeIO` TEXT NULL DEFAULT NULL,
    PRIMARY KEY (`SubjectId`(8))
) ENGINE = InnoDB;

-- Source: MakeTItrainIO.sql
CREATE TABLE `learning_c01_DataStore`.`TItrainIO` (
    `SubjectId` TEXT NOT NULL,
    `DateTime_Write` DATETIME NULL DEFAULT NULL,
    `ClientTimeZone` TEXT NULL DEFAULT NULL,
    `TItrainIO` TEXT NULL DEFAULT NULL,
    PRIMARY KEY (`SubjectId`(8))
) ENGINE = InnoDB;

-- Source: MakeUnfocuses.sql
CREATE TABLE `learning_c01_DataStore`.`Unfocuses` (
    `SubjectId` TEXT NULL DEFAULT NULL,
    `State` INT NULL DEFAULT NULL,
    `Href` TEXT NULL DEFAULT NULL,
    `DateTime_Unfocus` DATETIME NULL DEFAULT NULL
) ENGINE = InnoDB;

-- Source: MakeRecordConsetLog.sql
DELIMITER $$
CREATE PROCEDURE `learning_c01_DataStore`.`RecordConsentLog`(
    IN In_SubjectId TEXT,
    IN In_Initials TEXT,
    IN In_DateTime_Consent DATETIME
)
BEGIN
    IF (
        SELECT COUNT(SubjectId)
        FROM ConsentLog
        WHERE SubjectId = In_SubjectId
    ) = 0 THEN
        INSERT INTO ConsentLog (SubjectId, Initials, DateTime_Consent)
        VALUES (In_SubjectId, In_Initials, In_DateTime_Consent);
    ELSE
        UPDATE ConsentLog
        SET
            Initials = In_Initials,
            DateTime_Consent = In_DateTime_Consent
        WHERE SubjectId = In_SubjectId;
    END IF;
END $$
DELIMITER ;

-- Source: MakeRecordFeedback.sql
DELIMITER $$
CREATE PROCEDURE RecordFeedback(
    IN In_SubjectId TEXT,
    IN In_Feedback TEXT,
    IN In_DateTime_Feedback DATETIME
) BEGIN IF (
    SELECT
        COUNT(SubjectId)
    FROM
        Feedback
    WHERE
        SubjectId = In_SubjectId
) = 0 THEN
INSERT INTO
    Feedback (SubjectId, Feedback, DateTime_Feedback)
VALUES
    (In_SubjectId, In_Feedback, In_DateTime_Feedback);

ELSE
UPDATE
    Feedback
SET
    Feedback = In_Feedback,
    DateTime_Feedback = In_DateTime_Feedback
WHERE
    SubjectId = In_SubjectId;

END IF;
END $$
DELIMITER ;

-- Source: MakeRecordTIprobeIO.sql
DELIMITER $$
CREATE PROCEDURE `learning_c01_DataStore`.`RecordTIprobeIO`(
    IN In_SubjectId TEXT,
    IN In_DateTime_Write DATETIME,
    IN In_ClientTimeZone TEXT,
    IN In_TIprobeIO TEXT
)
BEGIN
    IF (
        SELECT COUNT(SubjectId)
        FROM TIprobeIO
        WHERE SubjectId = In_SubjectId
    ) = 0 THEN
        INSERT INTO TIprobeIO (
            SubjectId,
            DateTime_Write,
            ClientTimeZone,
            TIprobeIO
        )
        VALUES (
            In_SubjectId,
            In_DateTime_Write,
            In_ClientTimeZone,
            In_TIprobeIO
        );
    ELSE
        UPDATE TIprobeIO
        SET
            DateTime_Write = In_DateTime_Write,
            ClientTimeZone = In_ClientTimeZone,
            TIprobeIO = In_TIprobeIO
        WHERE SubjectId = In_SubjectId;
    END IF;
END $$
DELIMITER ;

-- Source: MakeRecordTItrainIO.sql
DELIMITER $$
CREATE PROCEDURE `learning_c01_DataStore`.`RecordTItrainIO`(
    IN In_SubjectId TEXT,
    IN In_DateTime_Write DATETIME,
    IN In_ClientTimeZone TEXT,
    IN In_TItrainIO TEXT
)
BEGIN
    IF (
        SELECT COUNT(SubjectId)
        FROM TItrainIO
        WHERE SubjectId = In_SubjectId
    ) = 0 THEN
        INSERT INTO TItrainIO (
            SubjectId,
            DateTime_Write,
            ClientTimeZone,
            TItrainIO
        )
        VALUES (
            In_SubjectId,
            In_DateTime_Write,
            In_ClientTimeZone,
            In_TItrainIO
        );
    ELSE
        UPDATE TItrainIO
        SET
            DateTime_Write = In_DateTime_Write,
            ClientTimeZone = In_ClientTimeZone,
            TItrainIO = In_TItrainIO
        WHERE SubjectId = In_SubjectId;
    END IF;
END $$
DELIMITER ;
