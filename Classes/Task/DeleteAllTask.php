<?php
namespace Bednarik\Cooluri\Task;

/**
    This file is part of CoolUri.

    CoolUri is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    CoolUri is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with CoolUri. If not, see <http://www.gnu.org/licenses/>.
 */

class DeleteAllTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

    /*
     * executed by scheduler
     */
    public function execute() {
        $manager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Bednarik\\Cooluri\\Manager\\Main');
        $_POST['delete'] = true;
        $manager->all();

        $dataHandler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
        $dataHandler->start(NULL,NULL);
        $dataHandler->clear_cacheCmd('pages');

        return true;
    }

}
