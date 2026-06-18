<?php
require_once __DIR__ . '/Credentials.php';

function DashboardEnsureSessionStarted()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function DashboardGetTitle()
{
    global $DashboardTitle;

    if (isset($DashboardTitle) && is_string($DashboardTitle) && $DashboardTitle !== '') {
        return $DashboardTitle;
    }

    return 'RepDist Participant Dashboard';
}

function DashboardGetSessionKey()
{
    global $DashboardSessionKey;

    if (isset($DashboardSessionKey) && is_string($DashboardSessionKey) && $DashboardSessionKey !== '') {
        return $DashboardSessionKey;
    }

    return 'repdist_dashboard_authenticated';
}

function DashboardIsAuthConfigured()
{
    global $DashboardPasswordSha256;

    return isset($DashboardPasswordSha256)
        && is_string($DashboardPasswordSha256)
        && preg_match('/^[a-f0-9]{64}$/i', $DashboardPasswordSha256) === 1;
}

function DashboardVerifyPassword($Password)
{
    global $DashboardPasswordSha256;

    if (!DashboardIsAuthConfigured()) {
        return false;
    }

    $ExpectedHash = strtolower(trim($DashboardPasswordSha256));
    $ActualHash = hash('sha256', strval($Password));

    return hash_equals($ExpectedHash, $ActualHash);
}

function DashboardLogin($Password)
{
    DashboardEnsureSessionStarted();

    if (!DashboardVerifyPassword($Password)) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION[DashboardGetSessionKey()] = true;
    $_SESSION[DashboardGetSessionKey() . '_at'] = time();

    return true;
}

function DashboardLogout()
{
    DashboardEnsureSessionStarted();
    unset($_SESSION[DashboardGetSessionKey()]);
    unset($_SESSION[DashboardGetSessionKey() . '_at']);
}

function DashboardIsAuthenticated()
{
    DashboardEnsureSessionStarted();

    return !empty($_SESSION[DashboardGetSessionKey()]);
}

function DashboardRequireAuthJson()
{
    if (DashboardIsAuthenticated()) {
        return;
    }

    http_response_code(401);
    echo json_encode(array(
        'Notice' => 'Dashboard authentication required.'
    ));
    exit;
}

function DashboardOpenConnection()
{
    global $Servername, $Username, $Password, $Dbname;

    $Conn = new mysqli($Servername, $Username, $Password, $Dbname);
    if ($Conn->connect_error) {
        throw new RuntimeException('Database connection failed: ' . $Conn->connect_error);
    }

    return $Conn;
}

function DashboardTableExists($Conn, $TableName)
{
    static $Cache = array();

    if (array_key_exists($TableName, $Cache)) {
        return $Cache[$TableName];
    }

    $EscapedTableName = mysqli_real_escape_string($Conn, $TableName);
    $Sql = "SHOW TABLES LIKE '$EscapedTableName'";
    $QueryRes = mysqli_query($Conn, $Sql);
    if ($QueryRes === false) {
        throw new RuntimeException('Failed to inspect dashboard tables.');
    }

    $Cache[$TableName] = mysqli_num_rows($QueryRes) > 0;
    mysqli_free_result($QueryRes);

    return $Cache[$TableName];
}

function DashboardValue($Row, $Key)
{
    if (!is_array($Row) || !array_key_exists($Key, $Row)) {
        return null;
    }

    return $Row[$Key];
}

function DashboardNormaliseText($Value)
{
    if ($Value === null) {
        return '';
    }

    return strval($Value);
}

function DashboardNormaliseDateTime($Value)
{
    $Text = trim(str_replace('T', ' ', DashboardNormaliseText($Value)));
    if ($Text === '' || $Text === '0000-00-00 00:00:00') {
        return '';
    }

    return $Text;
}

function DashboardDateTimeToEpoch($Value)
{
    $Text = DashboardNormaliseDateTime($Value);
    if ($Text === '') {
        return null;
    }

    try {
        $Stamp = new DateTimeImmutable($Text, new DateTimeZone('Europe/London'));
    } catch (Exception $Exception) {
        return null;
    }

    return $Stamp->getTimestamp();
}

function DashboardTruncate($Value, $Limit)
{
    $Text = trim(DashboardNormaliseText($Value));
    if ($Text === '' || strlen($Text) <= $Limit) {
        return $Text;
    }

    return substr($Text, 0, $Limit - 3) . '...';
}

function DashboardStateLabel($State)
{
    if ($State === null || $State === '') {
        return 'Unknown';
    }

    switch (intval($State)) {
        case -2:
            return 'Discontinued after leaving too long';
        case -1:
            return 'Discontinued after repeated tab switching';
        case 0:
            return 'Landed and awaiting consent';
        case 1:
            return 'Consented and awaiting registration';
        case 2:
            return 'Registered and reading training instructions';
        case 3:
            return 'Completing the training task';
        case 4:
            return 'Training complete and awaiting probe instructions';
        case 5:
            return 'Completing the probe task';
        case 6:
            return 'Study complete';
        default:
            return 'Unknown';
    }
}

function DashboardStateClass($State)
{
    if ($State === null || $State === '') {
        return 'unknown';
    }

    $State = intval($State);
    if ($State < 0) {
        return 'excluded';
    }
    if ($State === 6) {
        return 'complete';
    }
    if ($State === 3 || $State === 5) {
        return 'active';
    }

    return 'pending';
}

function DashboardStateTargetUrl($SubjectId, $State)
{
    $SubjectId = rawurlencode(DashboardNormaliseText($SubjectId));

    if ($State === null || $State === '') {
        return '';
    }

    switch (intval($State)) {
        case -2:
            return "./Coventry.html?SubjectId=$SubjectId&State=-2#";
        case -1:
            return "./Coventry.html?SubjectId=$SubjectId&State=-1#";
        case 0:
            return "./Consent.html?SubjectId=$SubjectId#";
        case 1:
            return "./Register.html?SubjectId=$SubjectId#";
        case 2:
            return "./Instruct.html?SubjectId=$SubjectId&TaskId=TItrain#";
        case 3:
            return "./TItrain.html?SubjectId=$SubjectId#";
        case 4:
            return "./Instruct.html?SubjectId=$SubjectId&TaskId=TIprobe#";
        case 5:
            return "./TIprobe.html?SubjectId=$SubjectId#";
        case 6:
            return "./Complete.html?SubjectId=$SubjectId#";
        default:
            return '';
    }
}

function DashboardLatestActivity($SourceMap)
{
    $Latest = array(
        'value' => '',
        'label' => '',
        'epoch' => null
    );

    foreach ($SourceMap as $Label => $Value) {
        $NormalisedValue = DashboardNormaliseDateTime($Value);
        $Epoch = DashboardDateTimeToEpoch($NormalisedValue);
        if ($Epoch === null) {
            continue;
        }

        if ($Latest['epoch'] === null || $Epoch > $Latest['epoch']) {
            $Latest['value'] = $NormalisedValue;
            $Latest['label'] = $Label;
            $Latest['epoch'] = $Epoch;
        }
    }

    return $Latest;
}

function DashboardBuildParticipantQuery($Conn)
{
    $Select = array(
        'r.PoolId',
        'r.SubjectId',
        'r.BMY',
        'r.Gender',
        'r.Handedness',
        'r.L1',
        'r.State',
        'r.GroupId',
        'r.ImgPerm',
        'r.DateTime_Landing',
        'r.DateTime_Consent',
        'r.DateTime_Register',
        'r.DateTime_TIinstr',
        'r.DateTime_TItrain',
        'r.DateTime_TIprobe'
    );
    $Joins = array();

    if (DashboardTableExists($Conn, 'TItrainIO')) {
        $Select[] = 'train.DateTime_Write AS TrainWriteTime';
        $Select[] = 'train.ClientTimeZone AS TrainClientTimeZone';
        $Joins[] = 'LEFT JOIN TItrainIO AS train ON train.SubjectId = r.SubjectId';
    }

    if (DashboardTableExists($Conn, 'TIprobeIO')) {
        $Select[] = 'probe.DateTime_Write AS ProbeWriteTime';
        $Select[] = 'probe.ClientTimeZone AS ProbeClientTimeZone';
        $Joins[] = 'LEFT JOIN TIprobeIO AS probe ON probe.SubjectId = r.SubjectId';
    }

    if (DashboardTableExists($Conn, 'Feedback')) {
        $Select[] = 'feedback.DateTime_Feedback AS FeedbackTime';
        $Select[] = 'feedback.Feedback AS FeedbackText';
        $Joins[] = 'LEFT JOIN Feedback AS feedback ON feedback.SubjectId = r.SubjectId';
    }

    if (DashboardTableExists($Conn, 'ConsentLog')) {
        $Select[] = 'consent.DateTime_Consent AS ConsentLogTime';
        $Joins[] = 'LEFT JOIN ConsentLog AS consent ON consent.SubjectId = r.SubjectId';
    }

    if (DashboardTableExists($Conn, 'Relandings')) {
        $Select[] = 'reland.RelandingCount';
        $Select[] = 'reland.LastReland';
        $Joins[] =
            'LEFT JOIN (' .
            'SELECT SubjectId, COUNT(*) AS RelandingCount, MAX(DateTime_Reland) AS LastReland ' .
            'FROM Relandings GROUP BY SubjectId' .
            ') AS reland ON reland.SubjectId = r.SubjectId';
    }

    if (DashboardTableExists($Conn, 'Unfocuses')) {
        $Select[] = 'unfocus.UnfocusCount';
        $Select[] = 'unfocus.LastUnfocus';
        $Joins[] =
            'LEFT JOIN (' .
            'SELECT SubjectId, COUNT(*) AS UnfocusCount, MAX(DateTime_Unfocus) AS LastUnfocus ' .
            'FROM Unfocuses GROUP BY SubjectId' .
            ') AS unfocus ON unfocus.SubjectId = r.SubjectId';
    }

    if (DashboardTableExists($Conn, 'InstructNaughtiness')) {
        $Select[] = 'naughty.NaughtyCount';
        $Select[] = 'naughty.LastNaughty';
        $Joins[] =
            'LEFT JOIN (' .
            'SELECT SubjectId, COUNT(*) AS NaughtyCount, MAX(DateTime_Naughty) AS LastNaughty ' .
            'FROM InstructNaughtiness GROUP BY SubjectId' .
            ') AS naughty ON naughty.SubjectId = r.SubjectId';
    }

    if (DashboardTableExists($Conn, 'Exclusions')) {
        $Select[] = 'exclusions.ExclusionCount';
        $Select[] = 'exclusions.LastExclude';
        $Joins[] =
            'LEFT JOIN (' .
            'SELECT SubjectId, COUNT(*) AS ExclusionCount, MAX(DateTime_Exclude) AS LastExclude ' .
            'FROM Exclusions GROUP BY SubjectId' .
            ') AS exclusions ON exclusions.SubjectId = r.SubjectId';
    }

    $Sql = "SELECT\n    " . implode(",\n    ", $Select) . "\nFROM Register AS r";
    if (count($Joins) > 0) {
        $Sql .= "\n" . implode("\n", $Joins);
    }

    $Sql .= "\nORDER BY r.DateTime_Landing DESC, r.SubjectId ASC";

    return $Sql;
}

function DashboardFetchParticipants($Conn)
{
    $Sql = DashboardBuildParticipantQuery($Conn);
    $QueryRes = mysqli_query($Conn, $Sql);
    if ($QueryRes === false) {
        throw new RuntimeException('Dashboard query failed to execute successfully.');
    }

    $Participants = array();
    $NowEpoch = time();

    while ($Row = mysqli_fetch_assoc($QueryRes)) {
        $State = DashboardValue($Row, 'State');
        $State = ($State === null || $State === '') ? null : intval($State);

        $ClientTimeZone = DashboardNormaliseText(DashboardValue($Row, 'TrainClientTimeZone'));
        if ($ClientTimeZone === '') {
            $ClientTimeZone = DashboardNormaliseText(DashboardValue($Row, 'ProbeClientTimeZone'));
        }

        $Latest = DashboardLatestActivity(array(
            'Probe payload received' => DashboardValue($Row, 'ProbeWriteTime'),
            'Training payload received' => DashboardValue($Row, 'TrainWriteTime'),
            'Feedback submitted' => DashboardValue($Row, 'FeedbackTime'),
            'Exclusion logged' => DashboardValue($Row, 'LastExclude'),
            'Instruction warning logged' => DashboardValue($Row, 'LastNaughty'),
            'Tab unfocus logged' => DashboardValue($Row, 'LastUnfocus'),
            'Relanding logged' => DashboardValue($Row, 'LastReland'),
            'Probe completion recorded' => DashboardValue($Row, 'DateTime_TIprobe'),
            'Training completion recorded' => DashboardValue($Row, 'DateTime_TItrain'),
            'Training instruction completion recorded' => DashboardValue($Row, 'DateTime_TIinstr'),
            'Registration recorded' => DashboardValue($Row, 'DateTime_Register'),
            'Consent log recorded' => DashboardValue($Row, 'ConsentLogTime'),
            'Consent recorded' => DashboardValue($Row, 'DateTime_Consent'),
            'Landing recorded' => DashboardValue($Row, 'DateTime_Landing')
        ));

        $SecondsSinceLastUpdate = $Latest['epoch'] === null
            ? null
            : max(0, $NowEpoch - $Latest['epoch']);
        $FeedbackText = DashboardNormaliseText(DashboardValue($Row, 'FeedbackText'));

        $Participants[] = array(
            'PoolId' => DashboardNormaliseText(DashboardValue($Row, 'PoolId')),
            'SubjectId' => DashboardNormaliseText(DashboardValue($Row, 'SubjectId')),
            'BMY' => DashboardNormaliseText(DashboardValue($Row, 'BMY')),
            'Gender' => DashboardNormaliseText(DashboardValue($Row, 'Gender')),
            'Handedness' => DashboardNormaliseText(DashboardValue($Row, 'Handedness')),
            'L1' => DashboardNormaliseText(DashboardValue($Row, 'L1')),
            'State' => $State,
            'StateLabel' => DashboardStateLabel($State),
            'StateClass' => DashboardStateClass($State),
            'GroupId' => DashboardNormaliseText(DashboardValue($Row, 'GroupId')),
            'ImgPerm' => DashboardNormaliseText(DashboardValue($Row, 'ImgPerm')),
            'DateTime_Landing' => DashboardNormaliseDateTime(DashboardValue($Row, 'DateTime_Landing')),
            'DateTime_Consent' => DashboardNormaliseDateTime(DashboardValue($Row, 'DateTime_Consent')),
            'DateTime_Register' => DashboardNormaliseDateTime(DashboardValue($Row, 'DateTime_Register')),
            'DateTime_TIinstr' => DashboardNormaliseDateTime(DashboardValue($Row, 'DateTime_TIinstr')),
            'DateTime_TItrain' => DashboardNormaliseDateTime(DashboardValue($Row, 'DateTime_TItrain')),
            'DateTime_TIprobe' => DashboardNormaliseDateTime(DashboardValue($Row, 'DateTime_TIprobe')),
            'ClientTimeZone' => $ClientTimeZone,
            'TargetUrl' => DashboardStateTargetUrl(DashboardValue($Row, 'SubjectId'), $State),
            'LastServerUpdate' => $Latest['value'],
            'LastServerUpdateSource' => $Latest['label'],
            'SecondsSinceLastUpdate' => $SecondsSinceLastUpdate,
            'RelandingCount' => intval(DashboardValue($Row, 'RelandingCount') ?: 0),
            'UnfocusCount' => intval(DashboardValue($Row, 'UnfocusCount') ?: 0),
            'NaughtyCount' => intval(DashboardValue($Row, 'NaughtyCount') ?: 0),
            'ExclusionCount' => intval(DashboardValue($Row, 'ExclusionCount') ?: 0),
            'HasFeedback' => $FeedbackText === '' ? 'No' : 'Yes',
            'FeedbackTimestamp' => DashboardNormaliseDateTime(DashboardValue($Row, 'FeedbackTime')),
            'FeedbackPreview' => DashboardTruncate($FeedbackText, 120)
        );
    }

    mysqli_free_result($QueryRes);

    return $Participants;
}

function DashboardBuildSummary($Participants)
{
    $Summary = array(
        'total' => 0,
        'active' => 0,
        'completed' => 0,
        'excluded' => 0,
        'stale24h' => 0,
        'stateBreakdown' => array()
    );

    foreach ($Participants as $Participant) {
        $Summary['total'] += 1;

        $State = $Participant['State'];
        $StateKey = $State === null ? 'unknown' : strval($State);
        if (!array_key_exists($StateKey, $Summary['stateBreakdown'])) {
            $Summary['stateBreakdown'][$StateKey] = array(
                'state' => $State,
                'label' => $Participant['StateLabel'],
                'count' => 0
            );
        }
        $Summary['stateBreakdown'][$StateKey]['count'] += 1;

        if ($State !== null && $State < 0) {
            $Summary['excluded'] += 1;
        } elseif ($State === 6) {
            $Summary['completed'] += 1;
        } else {
            $Summary['active'] += 1;
        }

        $SecondsSinceLastUpdate = $Participant['SecondsSinceLastUpdate'];
        if ($SecondsSinceLastUpdate !== null && $SecondsSinceLastUpdate >= 86400) {
            $Summary['stale24h'] += 1;
        }
    }

    $StateBreakdown = array_values($Summary['stateBreakdown']);
    usort($StateBreakdown, function ($Left, $Right) {
        if ($Left['count'] === $Right['count']) {
            return strcmp($Left['label'], $Right['label']);
        }

        return $Right['count'] <=> $Left['count'];
    });
    $Summary['stateBreakdown'] = $StateBreakdown;

    return $Summary;
}

function DashboardBuildPayload($Conn)
{
    $Participants = DashboardFetchParticipants($Conn);

    return array(
        'title' => DashboardGetTitle(),
        'generatedAt' => (new DateTimeImmutable('now', new DateTimeZone('Europe/London')))
            ->format('Y-m-d H:i:s'),
        'serverTimeZone' => 'Europe/London',
        'summary' => DashboardBuildSummary($Participants),
        'participants' => $Participants
    );
}
