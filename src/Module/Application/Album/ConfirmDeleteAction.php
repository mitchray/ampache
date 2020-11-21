<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 *  LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2020 Ampache.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=0);

namespace Ampache\Module\Application\Album;

use Ampache\Config\ConfigContainerInterface;
use Ampache\Config\ConfigurationKeyEnum;
use Ampache\Model\Catalog;
use Ampache\Model\ModelFactoryInterface;
use Ampache\Module\Application\ApplicationActionInterface;
use Ampache\Module\Application\Exception\AccessDeniedException;
use Ampache\Module\Authorization\GuiGatekeeperInterface;
use Ampache\Module\Util\Ui;
use Ampache\Module\Util\UiInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ConfirmDeleteAction implements ApplicationActionInterface
{
    public const REQUEST_KEY = 'confirm_delete';

    private ConfigContainerInterface $configContainer;

    private ModelFactoryInterface $modelFactory;

    private UiInterface $ui;

    public function __construct(
        ConfigContainerInterface $configContainer,
        ModelFactoryInterface $modelFactory,
        UiInterface $ui
    ) {
        $this->configContainer = $configContainer;
        $this->modelFactory    = $modelFactory;
        $this->ui              = $ui;
    }

    public function run(ServerRequestInterface $request, GuiGatekeeperInterface $gatekeeper): ?ResponseInterface
    {
        $response = null;

        require_once Ui::find_template('header.inc.php');
        
        if ($this->configContainer->isFeatureEnabled(ConfigurationKeyEnum::DEMO_MODE)) {
            // Show the Footer
            $this->ui->showQueryStats();
            $this->ui->showFooter();
            
            return $response;
        }

        $album = $this->modelFactory->createAlbum((int) $_REQUEST['album_id']);
        if (!Catalog::can_remove($album)) {
            throw new AccessDeniedException(
                sprintf('Unauthorized to remove the album `%d`', $album->id)
            );
        }

        if ($album->remove()) {
            show_confirmation(
                T_('No Problem'),
                T_('The Album has been deleted'),
                $this->configContainer->getWebPath()
            );
        } else {
            show_confirmation(
                T_('There Was a Problem'),
                T_('Couldn\'t delete this Album.'),
                $this->configContainer->getWebPath()
            );
        }
        
        // Show the Footer
        $this->ui->showQueryStats();
        $this->ui->showFooter();

        return $response;
    }
}