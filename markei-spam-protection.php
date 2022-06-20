<?php
/*
Plugin Name:  Markei.nl WordPress Spam Protection
Plugin URI:   https://github.com/markei/wordpress-spam-protection/
Description:  Simple spam filter for WordPress ContactForm 7 plugin
Version:      1.1.0
Author:       Markei.nl
Author URI:   https://www.markei.nl
License:      MIT
License URI:  https://opensource.org/licenses/MIT
Text Domain:  markei-security-protection
Domain Path:  /languages
*/

defined('ABSPATH') or die('Initialize WordPress-core first');

add_filter('wpcf7_validate', function ($result, $tags = null) {
    $score = [];
    $foundWords = [];

    foreach ($tags as $tag) {
        if (isset($_POST[$tag->name])) {
            $foundWordsInField = [];
            $score[$tag->name] = markei_spam_protection_number_of_spam_words_in_text($_POST[$tag->name], $foundWordsInField);
            $foundWords[$tag->name] = $foundWordsInField;
        }
    }

    if (array_sum($score) > 1) {
        foreach ($score as $tag => $fieldScore) {
            if ($fieldScore > 0) {
                $result->invalidate($tag, 'Dit bericht bevat woorden of delen van woorden die op de spamlijst staan. Uw bericht is daarom geweigerd. Gevonden woorden of delen van woorden: ' . implode(', ', $foundWords[$tag]));
            }
        }
    }

    return $result;
}, 9, 2);

function markei_spam_protection_number_of_spam_words_in_text($text, &$foundWords = [])
{
    $text = strtolower($text);

    $words =  explode(" ", $text);

    $ch = curl_init('https://antispam.markei.nl/database.json');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 7);
    $spamDbResponse = curl_exec($ch);
    if (curl_errno($ch) !== 0) {
        trigger_error('Can not reach antispam service', E_USER_WARNING);
        return -1;
    }
    curl_close($ch);

    $spamDb = json_decode($spamDbResponse, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        trigger_error('Can not decode antispam service response', E_USER_WARNING);
        return -1;
    }

    $spamScore = 0;
    $spamWords = [];
    foreach($words as $word) {
        if (array_key_exists($word, $spamDb)) {
            $spamScore = $spamScore + $spamDb[$word];
            $spamWords[] = $word;
        }
    }

    $foundWords = array_unique($spamWords);

    return $spamScore;
}
