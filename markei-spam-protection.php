<?php
/*
Plugin Name:  Markei.nl WordPress Spam Protection
Plugin URI:   https://github.com/markei/wordpress-spam-protection/
Description:  Simple spam filter for WordPress ContactForm 7 plugin
Version:      1.0.0
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

    $words = [
        'sex',
        'dating',
        'hookup',
        'asian',
        'ray-ban',
        'sunglass',
        'dollars',
        'girls',
        '.ru',
        'sissy',
        'women',
        '555',
        'medical',
        'oxford',
        'porno',
        'porn',
        'hypnosis',
        'pills',
        'viagra',
        'adult',
        'a href',
        'loans',
        'approval',
        '</a>',
        'credit',
        'writing',
        'binary',
        'payday',
        'captcha',
        'fitness',
        'refurbished',
        'bit.ly',
        '.tk',
        '$',
        'girls',
        '.pl',
        'profit',
        'geld',
        'webshop.se',
        '1000',
        '3000',
        '5000',
        'invest',
        'femme',
        'sexe',
        'sexywoman',
        'bit.ly',
        'amazinoffer',
        'loopia',
        'just click',
        'amazingoffer',
        'iphone',
        'airpods',
        'to.ht',
        'winiphone',
        'seksdating',
        'coins',
        'bitcoin',
        'bitcoins',
        'xyz',
        'btc',
        'blockchain',
        'secret',
        'secretflirters',
        'socialleader.eu',
        'socialleader',
        'girl',
        'sexe',
        'microsoft',
        'china',
        'oem',
        'dvd',
        'mail.ru',
        'usd',
        'million',
        'gratuits',
        'adultes',
        'canadiens',
        'traffic',
        'videomaker',
        'hacked',
        'money',
        'millionen',
        'clients',
        'weapons',
        'russia',
        'black',
        'inexpensive',
        'iphone',
        'x',
        'contest',
        'giveaway',
        'win',
        'free',
    ];

    $found = 0;

    foreach ($words as $word) {
        if (strpos($text, $word) !== false) {
            $found ++;
            $foundWords[] = $word;
        }
    }

    return $found;
}
