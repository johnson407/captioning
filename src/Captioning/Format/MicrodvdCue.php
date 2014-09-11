<?php

namespace Captioning\Format;

use Captioning\Cue;

class MicrodvdCue extends Cue
{
    public static function tc2ms($tc)
    {
        return $tc/25*1000;
    }
    
    public static function ms2tc($ms, $_separator = ',')
    {
        return $ms/1000*25;
    }

    public function getText($_stripTags = false, $_stripBasic = false, $_replacements = array())
    {
        parent::getText();

        if ($_stripTags) {
            return $this->getStrippedText($_stripBasic, $_replacements);
        } else {
            return $this->text;
        }
    }

    /**
     * Return the text without Advanced MicroDVD tags
     *
     * @param boolean $_stripBasic If true, <i>, <b> and <u> tags will be stripped
     * @param array $_replacements
     * @return string
     */
    public function getStrippedText($_stripBasic = false, $_replacements = array())
    {
        if ($_stripBasic) {
            $text = strip_tags($this->text);
        } else {
            $text = $this->text;
        }

        $patterns = "/{[^}]+}/";
        $repl = "";
        $text = preg_replace($patterns, $repl, $text);

        if (count($_replacements) > 0) {
            $text = str_replace(array_keys($_replacements), array_values($_replacements), $text);
            $text = iconv('UTF-8', 'UTF-8//IGNORE', $text);
        }

        return $text;
    }

    /**
     * Get the full timecode of the entry
     *
     * @return string
     */
    public function getTimeCodeString()
    {
        return '{'.$this->start.'}{'.$this->stop.'}';
    }

    public function strlen()
    {
        return mb_strlen($this->getText(true, true), 'UTF-8');
    }

    public function getReadingSpeed()
    {
        $dur = $this->getDuration();
        $dur = ($dur <= 500) ? 501 : $dur;

        return ($this->strlen() * 1000) / ($dur - 500);
    }
}
