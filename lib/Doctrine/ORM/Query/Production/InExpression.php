<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.phpdoctrine.org>.
 */

/**
 * InExpression = ["NOT"] "IN" "(" (Atom {"," Atom} | Subselect) ")"
 *
 * @package     Doctrine
 * @subpackage  Query
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author      Janne Vanhala <jpvanhal@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        http://www.phpdoctrine.org
 * @since       2.0
 * @version     $Revision$
 */
class Doctrine_Query_Production_InExpression extends Doctrine_Query_Production
{
    protected $_not;

    protected $_subselect;

    protected $_atoms = array();


    public function syntax($paramHolder)
    {
        // InExpression = ["NOT"] "IN" "(" (Atom {"," Atom} | Subselect) ")"
        $this->_not = false;

        if ($this->_isNextToken(Doctrine_Query_Token::T_NOT)) {
            $this->_parser->match(Doctrine_Query_Token::T_NOT);
            $this->_not = true;
        }

        $this->_parser->match(Doctrine_Query_Token::T_IN);

        $this->_parser->match('(');

        if ($this->_isNextToken(Doctrine_Query_Token::T_SELECT)) {
            $this->_subselect = $this->AST('Subselect', $paramHolder);
        } else {
            $this->_atoms[] = $this->AST('Atom', $paramHolder);

            while ($this->_isNextToken(',')) {
                $this->_parser->match(',');
                $this->_atoms[] = $this->AST('Atom', $paramHolder);
            }
        }

        $this->_parser->match(')');
    }


    public function buildSql()
    {
        return (($this->_not) ? 'NOT ' : '') . 'IN ('
             . (($this->_subselect !== null) ? $this->_subselect->buildSql() : implode(', ', $this->_mapAtoms()))
             . ')';
    }


    protected function _mapAtoms()
    {
        return array_map(array(&$this, '_mapAtom'), $this->_atoms);
    }


    protected function _mapAtom($value)
    {
        return $value->buildSql();
    }


    /* Getters */
    public function isNot()
    {
        return $this->_not;
    }


    public function getSubselect()
    {
        return $this->_subselect;
    }


    public function getAtoms()
    {
        return $this->_atoms;
    }
}