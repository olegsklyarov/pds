<?php

declare(strict_types=1);

namespace App;

class TemplatePowerParser
{
    var $tpl_base;              //Array( [filename/varcontent], [T_BYFILE/T_BYVAR] )
    var $tpl_include;           //Array( [filename/varcontent], [T_BYFILE/T_BYVAR] )
    var $tpl_count;

    var $parent = Array();    // $parent[{blockname}] = {parentblockname}
    var $defBlock = Array();

    var $ignore_stack;

    var $version;

    /**
     * TemplatePowerParser::TemplatePowerParser()
     *
     * @param $tpl_file
     * @param $type
     *
     * @access private
     */
    function __construct($tpl_file, $type)
    {
        $this->version = '3.0.2';

        $this->tpl_base = Array($tpl_file, $type);
        $this->tpl_count = 0;
        $this->ignore_stack = Array(false);
    }

    /**
     * TemplatePowerParser::__errorAlert()
     *
     * @param $message
     * @return string
     *
     * @access private
     */
    function __errorAlert($message)
    {
        return ('<br>' . $message . '<br>' . "\r\n");
    }

    /**
     * TemplatePowerParser::__prepare()
     *
     * @access private
     */
    function __prepare()
    {
        $this->defBlock[TP_ROOTBLOCK] = Array();
        $tplvar = $this->__prepareTemplate($this->tpl_base[0], $this->tpl_base[1]);

        $initdev["varrow"] = 0;
        $initdev["coderow"] = 0;
        $initdev["index"] = 0;
        $initdev["ignore"] = false;

        $this->__parseTemplate($tplvar, TP_ROOTBLOCK, $initdev);
        $this->__cleanUp();
    }

    /**
     * TemplatePowerParser::__cleanUp()
     * @access private
     */
    function __cleanUp()
    {
        for ($i = 0; $i <= $this->tpl_count; $i++) {
            $tplvar = 'tpl_rawContent' . $i;
            unset($this->{$tplvar});
        }
    }

    /**
     * TemplatePowerParser::__prepareTemplate()
     *
     * @param $tpl_file
     * @param $type
     * @return string
     *
     * @access private
     */
    function __prepareTemplate($tpl_file, $type)
    {
        $tplvar = 'tpl_rawContent' . $this->tpl_count;

        if ($type == T_BYVAR) {
            $this->{$tplvar}["content"] = preg_split("/\n/", $tpl_file, -1, PREG_SPLIT_DELIM_CAPTURE);
        } else {
            $this->{$tplvar}["content"] = @file($tpl_file) or
            die($this->__errorAlert('TemplatePower Error: Couldn\'t open [ ' . $tpl_file . ' ]!'));
        }

        $this->{$tplvar}["size"] = sizeof($this->{$tplvar}["content"]);

        $this->tpl_count++;

        return $tplvar;
    }

    /**
     * TemplatePowerParser::__parseTemplate()
     *
     * @param $tplvar
     * @param $blockname
     * @param $initdev
     * @return mixed
     *
     * @access private
     */
    function __parseTemplate($tplvar, $blockname, $initdev)
    {
        $coderow = $initdev["coderow"];
        $varrow = $initdev["varrow"];
        $index = $initdev["index"];

        while ($index < $this->{$tplvar}["size"]) {
            if (preg_match('/<!--[ ]?(START|END) IGNORE -->/', $this->{$tplvar}["content"][$index], $ignreg)) {
                if ($ignreg[1] == 'START') {
                    //$ignore = true;
                    array_push($this->ignore_stack, true);
                } else {
                    //$ignore = false;
                    array_pop($this->ignore_stack);
                }
            } else {
                if (!end($this->ignore_stack)) {
                    if (preg_match('/<!--[ ]?(START|END|INCLUDE|INCLUDESCRIPT|REUSE) BLOCK : (.+)-->/', $this->{$tplvar}["content"][$index], $regs)) {
                        //remove trailing and leading spaces
                        $regs[2] = trim($regs[2]);

                        if ($regs[1] == 'INCLUDE') {
                            $include_defined = true;

                            //check if the include file is assigned
                            if (isset($this->tpl_include[$regs[2]])) {
                                $tpl_file = $this->tpl_include[$regs[2]][0];
                                $type = $this->tpl_include[$regs[2]][1];
                            } else
                                if (file_exists($regs[2]))    //check if defined as constant in template
                                {
                                    $tpl_file = $regs[2];
                                    $type = T_BYFILE;
                                } else {
                                    $include_defined = false;
                                }

                            if ($include_defined) {
                                //initialize startvalues for recursive call
                                $initdev["varrow"] = $varrow;
                                $initdev["coderow"] = $coderow;
                                $initdev["index"] = 0;
                                $initdev["ignore"] = false;

                                $tplvar2 = $this->__prepareTemplate($tpl_file, $type);
                                $initdev = $this->__parseTemplate($tplvar2, $blockname, $initdev);

                                $coderow = $initdev["coderow"];
                                $varrow = $initdev["varrow"];
                            }
                        } else
                            if ($regs[1] == 'INCLUDESCRIPT') {
                                $include_defined = true;

                                //check if the includescript file is assigned by the assignInclude function
                                if (isset($this->tpl_include[$regs[2]])) {
                                    $include_file = $this->tpl_include[$regs[2]][0];
                                    $type = $this->tpl_include[$regs[2]][1];
                                } else
                                    if (file_exists($regs[2]))    //check if defined as constant in template
                                    {
                                        $include_file = $regs[2];
                                        $type = T_BYFILE;
                                    } else {
                                        $include_defined = false;
                                    }

                                if ($include_defined) {
                                    ob_start();

                                    if ($type == T_BYFILE) {
                                        if (!@include_once($include_file)) {
                                            die($this->__errorAlert('TemplatePower Error: Couldn\'t include script [ ' . $include_file . ' ]!'));
                                        }
                                    } else {
                                        eval("?>" . $include_file);
                                    }

                                    $this->defBlock[$blockname]["_C:$coderow"] = ob_get_contents();
                                    $coderow++;

                                    ob_end_clean();
                                }
                            } else
                                if ($regs[1] == 'REUSE') {
                                    //do match for 'AS'
                                    if (preg_match('/(.+) AS (.+)/', $regs[2], $reuse_regs)) {
                                        $originalbname = trim($reuse_regs[1]);
                                        $copybname = trim($reuse_regs[2]);

                                        //test if original block exist
                                        if (isset($this->defBlock[$originalbname])) {
                                            //copy block
                                            $this->defBlock[$copybname] = $this->defBlock[$originalbname];

                                            //tell the parent that he has a child block
                                            $this->defBlock[$blockname]["_B:" . $copybname] = '';

                                            //create index and parent info
                                            $this->index[$copybname] = 0;
                                            $this->parent[$copybname] = $blockname;
                                        } else {
                                            echo $this->__errorAlert('TemplatePower Error: Can\'t find block \'' . $originalbname . '\' to REUSE as \'' . $copybname . '\'');
                                        }
                                    } else {
                                        //so it isn't a correct REUSE tag, save as code
                                        $this->defBlock[$blockname]["_C:$coderow"] = $this->{$tplvar}["content"][$index];
                                        $coderow++;
                                    }
                                } else {
                                    if ($regs[2] == $blockname)     //is it the end of a block
                                    {
                                        break;
                                    } else                             //its the start of a block
                                    {
                                        //make a child block and tell the parent that he has a child
                                        $this->defBlock[$regs[2]] = Array();
                                        $this->defBlock[$blockname]["_B:" . $regs[2]] = '';

                                        //set some vars that we need for the assign functions etc.
                                        $this->index[$regs[2]] = 0;
                                        $this->parent[$regs[2]] = $blockname;

                                        //prepare for the recursive call
                                        $index++;
                                        $initdev["varrow"] = 0;
                                        $initdev["coderow"] = 0;
                                        $initdev["index"] = $index;
                                        $initdev["ignore"] = false;

                                        $initdev = $this->__parseTemplate($tplvar, $regs[2], $initdev);

                                        $index = $initdev["index"];
                                    }
                                }
                    } else                                                        //is it code and/or var(s)
                    {
                        //explode current template line on the curly bracket '{'
                        $sstr = explode('{', $this->{$tplvar}["content"][$index]);

                        reset($sstr);

                        if (current($sstr) != '') {
                            //the template didn't start with a '{',
                            //so the first element of the array $sstr is just code
                            $this->defBlock[$blockname]["_C:$coderow"] = current($sstr);
                            $coderow++;
                        }

                        while (next($sstr)) {
                            //find the position of the end curly bracket '}'
                            $pos = strpos(current($sstr), "}");

                            if (($pos !== false) && ($pos > 0)) {
                                //a curly bracket '}' is found
                                //and at least on position 1, to eliminate '{}'

                                //note: position 1 taken without '{', because we did explode on '{'

                                $strlength = strlen(current($sstr));
                                $varname = substr(current($sstr), 0, $pos);

                                if (strstr($varname, ' ')) {
                                    //the varname contains one or more spaces
                                    //so, it isn't a variable, save as code
                                    $this->defBlock[$blockname]["_C:$coderow"] = '{' . current($sstr);
                                    $coderow++;
                                } else {
                                    //save the variable
                                    $this->defBlock[$blockname]["_V:$varrow"] = $varname;
                                    $varrow++;

                                    //is there some code after the varname left?
                                    if (($pos + 1) != $strlength) {
                                        //yes, save that code
                                        $this->defBlock[$blockname]["_C:$coderow"] = substr(current($sstr), ($pos + 1), ($strlength - ($pos + 1)));
                                        $coderow++;
                                    }
                                }
                            } else {
                                //no end curly bracket '}' found
                                //so, the curly bracket is part of the text. Save as code, with the '{'
                                $this->defBlock[$blockname]["_C:$coderow"] = '{' . current($sstr);
                                $coderow++;
                            }
                        }
                    }
                } else {
                    $this->defBlock[$blockname]["_C:$coderow"] = $this->{$tplvar}["content"][$index];
                    $coderow++;
                }
            }

            $index++;
        }

        $initdev["varrow"] = $varrow;
        $initdev["coderow"] = $coderow;
        $initdev["index"] = $index;

        return $initdev;
    }


    /**
     * TemplatePowerParser::version()
     *
     * @access public
     */
    function version()
    {
        return $this->version;
    }

    /**
     * TemplatePowerParser::assignInclude()
     *
     * @param $iblockname
     * @param $value
     * @param $type
     *
     * @access public
     */
    function assignInclude($iblockname, $value, $type = T_BYFILE)
    {
        $this->tpl_include["$iblockname"] = Array($value, $type);
    }
}
