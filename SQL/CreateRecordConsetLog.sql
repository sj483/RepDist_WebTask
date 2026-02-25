DELIMITER $ $ CREATE PROCEDURE RecordConsentLog(
	IN In_SubjectId TEXT,
	IN In_Initials TEXT,
	IN In_DateTime_Consent DATETIME
) BEGIN IF (
	SELECT
		COUNT(SubjectId)
	FROM
		ConsentLog
	WHERE
		SubjectId = In_SubjectId
) = 0 THEN
INSERT INTO
	ConsentLog (SubjectId, Initials, DateTime_Consent)
VALUES
	(In_SubjectId, In_Initials, In_DateTime_Consent);

ELSE
UPDATE
	ConsentLog
SET
	Initials = In_Initials,
	DateTime_Consent = In_DateTime_Consent
WHERE
	SubjectId = In_SubjectId;

END IF;

END $ $ DELIMITER;