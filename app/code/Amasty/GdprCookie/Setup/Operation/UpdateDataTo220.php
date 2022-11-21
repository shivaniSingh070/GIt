<?php
declare(strict_types=1);

namespace Amasty\GdprCookie\Setup\Operation;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;

class UpdateDataTo220
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var InstallCookieData
     */
    private $installCookieData;

    public function __construct(
        State $appState,
        InstallCookieData $installCookieData
    ) {
        $this->appState = $appState;
        $this->installCookieData = $installCookieData;
    }

    /**
     * @throws \Exception
     */
    public function upgrade()
    {
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, [$this, 'updateModuleData']);
    }

    public function updateModuleData()
    {
        $this->installCookieData->addCookieInformation(true);
    }
}
