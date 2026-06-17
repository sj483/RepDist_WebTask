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