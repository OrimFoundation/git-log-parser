<?php

class GitCommit
{

    public $commit;

    public $authorName;

    public $authorEmail;

    public $dateTime;

    protected $rawCommitMessage;

    protected $commitMessage;

    public $arrRawCommitMessage = array();

    public function getCommitMessage()
    {

        $string = implode(PHP_EOL, $this->arrRawCommitMessage);

        if (strpos($string, "***") === 0) {
            return PHP_EOL . $string . PHP_EOL;
        } elseif (strpos($string, "---") === 0) {
            return PHP_EOL . $string . PHP_EOL;
        }


        $string = preg_replace('/' . PHP_EOL . '[-]{2,3}' . PHP_EOL . '/', PHP_EOL . '    - ', $string, -1, $cnt);

        if ($cnt > 0) {
            $string .= PHP_EOL;
        }

        $string = preg_replace('/' . PHP_EOL . '    - - /', PHP_EOL . '    - ', $string);
        $string = preg_replace('/' . PHP_EOL . '- /', PHP_EOL . '    - ', $string);

        return ' - ' . $string;
    }
}

$arrObj       = array();
$arrArguments = getopt('d:l:');

if (array_key_exists('d', $arrArguments)) {
    chdir($arrArguments['d']);
}
$l = '';
if (array_key_exists('l', $arrArguments)) {
    $l = $arrArguments['l'];
}

exec('git log ' . $l, $arrOutput);

$regExCommit = '/commit (?<commit>[a-zA-Z0-9]*)$/';
$regExAuthor = '/Author:(?<name>.*)<(?<email>.*)>$/';
$regExDate   = '/Date: (?<datetime>.*)$/';

$start     = true;
$objCommit = new GitCommit();

foreach ($arrOutput as $line) {

    if (preg_match($regExCommit, $line, $arrMatches)) {

        if ($start) {
            $start = false;
        } else {
            $arrObj[]          = $objCommit;
            $objCommit         = new GitCommit();
            $objCommit->commit = trim($arrMatches['commit']);
        }

    } elseif (preg_match($regExAuthor, $line, $arrMatches)) {

        $objCommit->authorName  = trim($arrMatches['name']);
        $objCommit->authorEmail = trim($arrMatches['email']);

    } elseif (preg_match($regExDate, $line, $arrMatches)) {

        /*
        $ts = strtotime(trim($arrMatches['datetime']));
        $obj                 = new DateTime();
        $objCommit->dateTime = $obj;
        */

    } else {

        $line = trim($line);

        if ($line !== '') {
            $objCommit->arrRawCommitMessage[] = $line;
        }
    }

}

foreach ($arrObj as $objCommit) {

    echo $objCommit->getCommitMessage() . PHP_EOL;

}


?>