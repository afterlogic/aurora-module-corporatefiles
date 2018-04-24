<?php
/**
 * This code is licensed under AGPLv3 license or AfterLogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\CorporateFiles;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 *
 * @package Modules
 */
class Module extends \Aurora\Modules\PersonalFiles\Module
{
	protected static $sStorageType = 'corporate';
	
	public function init() 
	{
		parent::init();
		
		$this->subscribeEvent('Files::GetQuota::after', array($this, 'onAfterGetQuota'));
		
		$this->RemoveEntries(
			array(
				'upload',
				'download-file'
			)
		);
	}
	
	public function onAfterGetQuota($aArgs, &$mResult)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
		if ($this->checkStorageType($aArgs['Type']))
		{
			$sPublicUserId = \Aurora\System\Api::getUserPublicIdById($aArgs['UserId']);
			
			$oDirectory = $this->oApiFilesManager->getDirectory($sPublicUserId, $aArgs['Type'], '/');
			$iSize = 0;
			if ($oDirectory)
			{
				$sPath = $oDirectory->getPath();
				
				$aSizeInfo = \Aurora\System\Utils::GetDirectorySize($sPath);
				$iSize = $aSizeInfo['size'];
			}
			
			$mResult = array(
				'Used' => $iSize,
				'Limit' => $this->getConfig('UserSpaceLimitMb', 0) * 1024 * 1024
			);
			
			return true;
		}
	}
}
