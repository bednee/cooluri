<?php
namespace Bednarik\Cooluri\Tests\Unit;

class CoolUriTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

    /**
     * @return void
     */
    public function setUp() {
        $GLOBALS['LANG'] = $this->getMock('TYPO3\\CMS\\Lang\\LanguageService', array('sL'));
        $GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection', array('sql_query'), array(), '', FALSE);
        $GLOBALS['TYPO3_DB']->expects($this->any())->method('sql_query')->willReturn(FALSE);
    }

    public function testAll() {
        $lt = \Bednarik\Cooluri\Core\Translate::getInstance(dirname(__FILE__).'/Resources/CoolUriConf.xml');
        $lt->params2cool(Array('id'=>1));
        echo $lt;
    }


}