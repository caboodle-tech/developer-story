<?php
// https://www.php.net/manual/en/function.strip-tags.php#86964
function strip_tags_content($text, $tags = '', $invert = FALSE) {

    preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
    echo '<pre>';
    print_r($tags);
    echo '</pre>';
    $tags = array_unique($tags[1]);

    echo '<pre>';
    print_r($tags);
    echo '</pre>';
     
    if(is_array($tags) AND count($tags) > 0) {
      if($invert == FALSE) {
        return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
      }
      else {
        return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
      }
    }
    elseif($invert == FALSE) {
      return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
    }
    return $text;
}

$html = '<p stype="color:black;">This is a <a href="https://website.com" target="_blank">test</a> run.</p>';

echo '[' . strip_tags_content($html, '<p>', true) . ']';
  