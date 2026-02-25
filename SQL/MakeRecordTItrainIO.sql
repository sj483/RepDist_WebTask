DELIMITER $ $ CREATE PROCEDURE RecordTItrainIO(
	IN In_SubjectId TEXT,
	IN In_DateTime_Write DATETIME,
	IN In_ClientTimeZone TEXT,
	IN In_TItrainIO TEXT
) BEGIN IF (
	SELECT
		COUNT(SubjectId)
	FROM
		TItrainIO
	WHERE
		SubjectId = In_SubjectId
) = 0 THEN
INSERT INTO
	TItrainIO (
		SubjectId,
		DateTime_Write,
		ClientTimeZone,
		TItrainIO
	)
VALUES
	(
		In_SubjectId,
		In_DateTime_Write,
		In_ClientTimeZone,
		In_TItrainIO
	);

ELSE
UPDATE
	TItrainIO
SET
	DateTime_Write = In_DateTime_Write,
	ClientTimeZone = In_ClientTimeZone,
	TItrainIO = In_TItrainIO
WHERE
	SubjectId = In_SubjectId;

END IF;

END $ $ DELIMITER;