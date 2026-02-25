DELIMITER $ $ CREATE PROCEDURE RecordTIprobeIO(
	IN In_SubjectId TEXT,
	IN In_DateTime_Write DATETIME,
	IN In_ClientTimeZone TEXT,
	IN In_TIprobeIO TEXT
) BEGIN IF (
	SELECT
		COUNT(SubjectId)
	FROM
		TIprobeIO
	WHERE
		SubjectId = In_SubjectId
) = 0 THEN
INSERT INTO
	TIprobeIO (
		SubjectId,
		DateTime_Write,
		ClientTimeZone,
		TIprobeIO
	)
VALUES
	(
		In_SubjectId,
		In_DateTime_Write,
		In_ClientTimeZone,
		In_TIprobeIO
	);

ELSE
UPDATE
	TIprobeIO
SET
	DateTime_Write = In_DateTime_Write,
	ClientTimeZone = In_ClientTimeZone,
	TIprobeIO = In_TIprobeIO
WHERE
	SubjectId = In_SubjectId;

END IF;

END $ $ DELIMITER;