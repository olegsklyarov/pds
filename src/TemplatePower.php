<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | TemplatePower:                                                       |
// | offers you the ability to separate your PHP code and your HTML       |
// +----------------------------------------------------------------------+
// |                                                                      |
// | Copyright (C) 2001,2002  R.P.J. Velzeboer, The Netherlands           |
// |                                                                      |
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License          |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to the Free Software          |
// | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA            |
// | 02111-1307, USA.                                                     |
// |                                                                      |
// | Author: R.P.J. Velzeboer, rovel@codocad.nl   The Netherlands         |
// |                                                                      |
// +----------------------------------------------------------------------+
// | http://templatepower.codocad.com                                     |
// +----------------------------------------------------------------------+
//
// $Id: Version 3.0.2$

namespace App;

define("T_BYFILE", 0);
define("T_BYVAR", 1);

define("TP_ROOTBLOCK", '_ROOT');



final class TemplatePower extends TemplatePowerParser
{
    var $index = Array();        // $index[{blockname}]  = {indexnumber}
    var $content = Array();

    var $currentBlock;
    var $showUnAssigned;
    var $serialized;
    var $globalvars = Array();
    var $prepared;

    /**
     * TemplatePower::TemplatePower()
     *
     * @param $tpl_file
     * @param $type
     *
     * @access public
     */
    function __construct($tpl_file = '', $type = T_BYFILE)
    {
        TemplatePowerParser::__construct(__DIR__ . "/../src/templates/$tpl_file", $type);
        $this->prepared = false;
        $this->showUnAssigned = false;
        $this->serialized = false;  //added: 26 April 2002
    }

    /**
     * TemplatePower::__deSerializeTPL()
     *
     * @param $stpl_file
     * @param $type
     *
     * @access private
     */
    function __deSerializeTPL($stpl_file, $type)
    {
        if ($type == T_BYFILE) {
            $serializedTPL = @file($stpl_file) or
            die($this->__errorAlert('TemplatePower Error: Can\'t open [ ' . $stpl_file . ' ]!'));
        } else {
            $serializedTPL = $stpl_file;
        }

        $serializedStuff = unserialize(join('', $serializedTPL));

        $this->defBlock = $serializedStuff["defBlock"];
        $this->index = $serializedStuff["index"];
        $this->parent = $serializedStuff["parent"];
    }

    /**
     * TemplatePower::__makeContentRoot()
     *
     * @access private
     */
    function __makeContentRoot()
    {
        $this->content[TP_ROOTBLOCK . "_0"][0] = Array(TP_ROOTBLOCK);
        $this->currentBlock = &$this->content[TP_ROOTBLOCK . "_0"][0];
    }

    /**
     * TemplatePower::__assign()
     *
     * @param $varname
     * @param $value
     *
     * @access private
     */
    function __assign($varname, $value)
    {
        if (sizeof($regs = explode('.', $varname)) == 2)  //this is faster then preg_match
        {
            $ind_blockname = $regs[0] . '_' . $this->index[$regs[0]];

            $lastitem = sizeof($this->content[$ind_blockname]);

            $lastitem > 1 ? $lastitem-- : $lastitem = 0;

            $block = &$this->content[$ind_blockname][$lastitem];
            $varname = $regs[1];
        } else {
            $block = &$this->currentBlock;
        }

        $block["_V:$varname"] = $value;

    }

    /**
     * TemplatePower::__assignGlobal()
     *
     * @param $varname
     * @param $value
     *
     * @access private
     */
    function __assignGlobal($varname, $value)
    {
        $this->globalvars[$varname] = $value;
    }


    /**
     * TemplatePower::__outputContent()
     *
     * @param $blockname
     *
     * @access private
     */
    function __outputContent($blockname)
    {
        $numrows = sizeof($this->content[$blockname]);

        for ($i = 0; $i < $numrows; $i++) {
            $defblockname = $this->content[$blockname][$i][0];

            for (reset($this->defBlock[$defblockname]); $k = key($this->defBlock[$defblockname]); next($this->defBlock[$defblockname])) {
                if ($k[1] == 'C') {
                    print($this->defBlock[$defblockname][$k]);
                } else
                    if ($k[1] == 'V') {
                        $defValue = $this->defBlock[$defblockname][$k];

                        if (!isset($this->content[$blockname][$i]["_V:" . $defValue])) {
                            if (isset($this->globalvars[$defValue])) {
                                $value = $this->globalvars[$defValue];
                            } else {
                                if ($this->showUnAssigned) {
                                    //$value = '{'. $this->defBlock[ $defblockname ][$k] .'}';
                                    $value = '{' . $defValue . '}';
                                } else {
                                    $value = '';
                                }
                            }
                        } else {
                            $value = $this->content[$blockname][$i]["_V:" . $defValue];
                        }

                        print($value);

                    } else
                        if ($k[1] == 'B') {
                            if (isset($this->content[$blockname][$i][$k])) {
                                $this->__outputContent($this->content[$blockname][$i][$k]);
                            }
                        }
            }
        }
    }

    function __printVars()
    {
        var_dump($this->defBlock);
        print("<br>--------------------<br>");
        var_dump($this->content);
    }


    /**********
     * public members
     ***********/

    /**
     * TemplatePower::serializedBase()
     *
     * @access public
     */
    function serializedBase()
    {
        $this->serialized = true;
        $this->__deSerializeTPL($this->tpl_base[0], $this->tpl_base[1]);
    }

    /**
     * TemplatePower::showUnAssigned()
     *
     * @param $state
     *
     * @access public
     */
    function showUnAssigned($state = true)
    {
        $this->showUnAssigned = $state;
    }

    /**
     * TemplatePower::prepare()
     *
     * @access public
     */
    function prepare()
    {
        if (!$this->serialized) {
            TemplatePowerParser::__prepare();
        }

        $this->prepared = true;

        $this->index[TP_ROOTBLOCK] = 0;
        $this->__makeContentRoot();
    }

    /**
     * TemplatePower::newBlock()
     *
     * @param $blockname
     *
     * @access public
     */
    function newBlock($blockname)
    {
        $parent = &$this->content[$this->parent[$blockname] . '_' . $this->index[$this->parent[$blockname]]];

        $lastitem = sizeof($parent);
        $lastitem > 1 ? $lastitem-- : $lastitem = 0;

        $ind_blockname = $blockname . '_' . $this->index[$blockname];

        if (!isset($parent[$lastitem]["_B:$blockname"])) {
            //ok, there is no block found in the parentblock with the name of {$blockname}

            //so, increase the index counter and create a new {$blockname} block
            $this->index[$blockname] += 1;

            $ind_blockname = $blockname . '_' . $this->index[$blockname];

            if (!isset($this->content[$ind_blockname])) {
                $this->content[$ind_blockname] = Array();
            }

            //tell the parent where his (possible) children are located
            $parent[$lastitem]["_B:$blockname"] = $ind_blockname;
        }

        //now, make a copy of the block defenition
        $blocksize = sizeof($this->content[$ind_blockname]);

        $this->content[$ind_blockname][$blocksize] = Array($blockname);

        //link the current block to the block we just created
        $this->currentBlock = &$this->content[$ind_blockname][$blocksize];
    }

    /**
     * TemplatePower::assignGlobal()
     *
     * @param $varname
     * @param $value
     *
     * @access public
     */
    function assignGlobal($varname, $value = '')
    {
        if (is_array($varname)) {
            foreach ($varname as $var => $value) {
                $this->__assignGlobal($var, $value);
            }
        } else {
            $this->__assignGlobal($varname, $value);
        }
    }


    /**
     * TemplatePower::assign()
     *
     * @param $varname
     * @param $value
     *
     * @access public
     */
    function assign($varname, $value = '')
    {
        if (is_array($varname)) {
            foreach ($varname as $var => $value) {
                $this->__assign($var, $value);
            }
        } else {
            $this->__assign($varname, $value);
        }
    }

    /**
     * TemplatePower::gotoBlock()
     *
     * @param $blockname
     *
     * @access public
     */
    function gotoBlock($blockname)
    {
        if (isset($this->defBlock[$blockname])) {
            $ind_blockname = $blockname . '_' . $this->index[$blockname];

            //get lastitem indexnumber
            $lastitem = sizeof($this->content[$ind_blockname]);

            $lastitem > 1 ? $lastitem-- : $lastitem = 0;

            //link the current block
            $this->currentBlock = &$this->content[$ind_blockname][$lastitem];
        }
    }

    /**
     * TemplatePower::getVarValue()
     *
     * @param $varname
     * @return string
     *
     * @access public
     */
    function getVarValue($varname)
    {
        if (sizeof($regs = explode('.', $varname)) == 2)  //this is faster then preg_match
        {
            $ind_blockname = $regs[0] . '_' . $this->index[$regs[0]];

            $lastitem = sizeof($this->content[$ind_blockname]);

            $lastitem > 1 ? $lastitem-- : $lastitem = 0;

            $block = &$this->content[$ind_blockname][$lastitem];
            $varname = $regs[1];
        } else {
            $block = &$this->currentBlock;
        }

        return $block["_V:$varname"];
    }

    /**
     * TemplatePower::printToScreen()
     *
     * @access public
     */
    function printToScreen()
    {
        if ($this->prepared) {
            $this->__outputContent(TP_ROOTBLOCK . '_0');
        } else {
            echo $this->__errorAlert('TemplatePower Error: Template isn\'t prepared!');
        }
    }

    /**
     * TemplatePower::getOutputContent()
     *
     * @access public
     */
    function getOutputContent()
    {
        ob_start();

        $this->printToScreen();

        $content = ob_get_contents();

        ob_end_clean();

        return $content;
    }
}
