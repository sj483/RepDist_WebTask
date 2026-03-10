<?php
function MakeAssignment($SubjectId) {
    $Assignment = array();

    // Get the SubjectInt
    $SubjectId = strtolower($SubjectId);
    $SubjectInt = intval($SubjectId, 36);

    // Set the Seed
    srand($SubjectInt % 4294967295);

    // Choose a group
    $Groups  = array(
        'Ani','Art','Fac',
        'Foo','Lin','Obj',
        'Pla','Spa','Tex');
    $GroupId = $Groups[rand(0, 8)];
    $Assignment['GroupId'] = $GroupId;

    // Construct an (un-shuffled) array of ImgNames
    $ImgNames = array();
    for ($ii = 0; $ii < 6; $ii++) {
        $ImgNames[$ii] = sprintf("%s%01d", $GroupId, $ii);
    }

    // Construct a shuffled image permutation
    shuffle($ImgNames);
    $ImgPerm['A'] = $ImgNames[0];
    $ImgPerm['B'] = $ImgNames[1];
    $ImgPerm['C'] = $ImgNames[2];
    $ImgPerm['D'] = $ImgNames[3];
    $ImgPerm['E'] = $ImgNames[4];
    $ImgPerm['F'] = $ImgNames[5];
    $Assignment['ImgPerm'] = $ImgPerm;

    return $Assignment;
}