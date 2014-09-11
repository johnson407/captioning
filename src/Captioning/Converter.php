<?php

namespace Captioning;

use Captioning\Format\SubripFile;
use Captioning\Format\SubripCue;
use Captioning\Format\WebvttFile;
use Captioning\Format\WebvttCue;
use Captioning\Format\SubstationalphaFile;
use Captioning\Format\SubstationalphaCue;
use Captioning\Format\MicrodvdCue;
use Captioning\Format\MicrodvdFile;

class Converter
{
    /* microdvd converters*/
    public static function microdvd2subrip(MicrodvdFile $_microdvd){
        $srt = new SubripFile();
        foreach ($_microdvd->getCues() as $cue) {
            $text = str_replace('|', "\r\n", $cue->getText(true));
            $srt->addCue($text, SubripCue::ms2tc($cue->getStartMS()), SubripCue::ms2tc($cue->getStopMS()));
        }
        return $srt;
    }
    
    public static function microdvd2webvtt(SubripFile $_srt)
    {
        $vtt = new WebvttFile();
        foreach ($_srt->getCues() as $cue) {
            $text = str_replace('|', "\r\n", $cue->getText(true));
            $vtt->addCue($text, SubripCue::ms2tc($cue->getStartMS(), '.'), SubripCue::ms2tc($cue->getStopMS(), '.'));
        }

        return $vtt;
    }

    /* subrip converters */
    public static function subrip2webvtt(SubripFile $_srt)
    {
        $vtt = new WebvttFile();
        foreach ($_srt->getCues() as $cue) {
            $vtt->addCue($cue->getText(true), SubripCue::ms2tc($cue->getStartMS(), '.'), SubripCue::ms2tc($cue->getStopMS(), '.'));
        }

        return $vtt;
    }

    public static function subrip2substationalpha(SubripFile $_srt)
    {
        $ass = new SubstationalphaFile();
        foreach ($_srt->getCues() as $cue) {
            $search  = array("\r\n", "\r", "\n", '<i>', '</i>', '<b>', '</b>', '<u>', '</u>');
            $replace = array('\N', '\N', '\N', '{\i1}', '{\i0}', '{\b1}', '{\b0}', '{\u1}', '{\u0}');
            $text    = str_replace($search, $replace, $cue->getText());

            $search_regex = array(
                '#<font color="?\#?([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})"?>(.+)</font>#is'
            );
            $replace_regex = array(
                '{\c&H$3$2$1&}$4'
            );
            $text = preg_replace($search_regex, $replace_regex, $text);

            $ass->addCue($text, SubstationalphaCue::ms2tc($cue->getStartMS()), SubstationalphaCue::ms2tc($cue->getStopMS()));
        }

        return $ass;
    }

    /* webvtt converters */
    public static function webvtt2subrip(WebvttFile $_vtt)
    {
        $srt = new SubripFile();
        foreach ($_vtt->getCues() as $cue) {
            $srt->addCue($cue->getText(), SubripCue::ms2tc($cue->getStartMS()), SubripCue::ms2tc($cue->getStopMS()));
        }

        return $srt;
    }

    public static function webvtt2substationalpha(WebvttFile $_vtt)
    {
        return self::subrip2substationalpha(self::webvtt2subrip($_vtt));
    }

    /* substation alpha converters */
    public static function substationalpha2subrip(SubstationalphaFile $_ass)
    {
        $srt = new SubripFile();
        foreach ($_ass->getCues() as $cue) {
            $search  = array('\N', '\N', '\N', '{\i1}', '{\i0}', '{\b1}', '{\b0}', '{\u1}', '{\u0}');
            $replace = array("\r\n", "\r", "\n", '<i>', '</i>', '<b>', '</b>', '<u>', '</u>');
            $text    = str_replace($search, $replace, $cue->getText());

            $search_regex = array(
                '#{\\c&H([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})\}(.+)#is'
            );
            $replace_regex = array(
                '<font color="#$3$2$1">$4</font>'
            );
            $text = preg_replace($search_regex, $replace_regex, $text);

            $srt->addCue($text, SubripCue::ms2tc($cue->getStartMS()), SubripCue::ms2tc($cue->getStopMS()));
        }

        return $srt;
    }

    public static function substationalpha2webvtt(SubstationalphaFile $_ass)
    {
        return self::subrip2webvtt(self::substationalpha2subrip($_ass));
    }
}
