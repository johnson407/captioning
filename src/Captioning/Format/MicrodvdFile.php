<?php

namespace Captioning\Format;

use Captioning\File;

class MicrodvdFile extends File
{
    const PATTERN = '#{([0-9]+)}{([0-9]+)}(.*)#';

    protected $lineEnding = File::WINDOWS_LINE_ENDING;

    public function parse()
    {
        $matches = array();
        $res = preg_match_all(self::PATTERN, $this->fileContent, $matches);

        if (!$res || $res == 0) {
            throw new \Exception($this->filename.' is not a proper .sub file.');
        }

        $entries_count = count($matches[1]);

        for ($i = 0; $i < $entries_count; $i++) {
            $cue = new MicrodvdCue($matches[1][$i], $matches[2][$i], $matches[3][$i]);
            $cue->setLineEnding($this->lineEnding);
            $this->addCue($cue);
        }

        return $this;
    }

    /**
     * Builds file content
     *
     * @param boolean $_stripTags If true, {\...} tags will be stripped
     * @param boolean $_stripBasic If true, <i>, <b> and <u> tags will be stripped
     * @param array $_replacements
     */
    public function build($_stripTags = false, $_stripBasic = false, $_replacements = array())
    {
        $this->buildPart(0, $this->getCuesCount()-1, $_stripTags, $_stripBasic, $_replacements);

        return $this;
    }

    /**
     * Builds file content from entry $_from to entry $_to
     *
     * @param int $_from Id of the first entry
     * @param int $_to Id of the last entry
     * @param boolean $_stripTags If true, {\...} tags will be stripped
     * @param boolean $_stripBasic If true, <i>, <b> and <u> tags will be stripped
     * @param array $_replacements
     */
    public function buildPart($_from, $_to, $_stripTags = false, $_stripBasic = false, $_replacements = array())
    {
        $this->sortCues();
        
        $i = 1;
        $buffer = "";
        if ($_from < 0 || $_from >= $this->getCuesCount()) {
            $_from = 0;
        }

        if ($_to < 0 || $_to >= $this->getCuesCount()) {
            $_to = $this->getCuesCount()-1;
        }

        for ($j = $_from; $j <= $_to; $j++) {
            $buffer .= $this->getCue($j)->getTimeCodeString();
            $buffer .= $this->getCue($j)->getText($_stripTags, $_stripBasic, $_replacements).$this->lineEnding;
            $i++;
        }
        
        $this->fileContent = $buffer;

        return $this;
    }
}
