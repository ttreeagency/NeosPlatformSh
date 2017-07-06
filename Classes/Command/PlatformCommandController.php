<?php
declare(strict_types=1);

namespace Ttree\NeosPlatformSh\Command;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Neos\Domain\Model\User;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\Domain\Service\SiteImportService;
use Neos\Neos\Domain\Service\UserService;

/**
 * @Flow\Scope("singleton")
 */
class PlatformCommandController extends CommandController
{
    /**
     * @var SiteRepository
     * @Flow\Inject
     */
    protected $siteRepository;

    /**
     * @var SiteImportService
     * @Flow\Inject
     */
    protected $siteImportService;

    /**
     * @var UserService
     * @Flow\Inject
     */
    protected $userService;

    /**
     * Import a site package and create a dummy user
     * @param string $packageKey Site package to import
     * @param string|null $username Default admin username
     * @param string|null $password Default admin password
     */
    public function setupCommand(string $packageKey = null, string $username = null, string $password = null) :void
    {
        $packageKey = $packageKey ?: 'Neos.Demo';
        $username = $username ?: 'admin';
        $password = $password ?: 'changeme';

        $site = $this->siteRepository->findOneBySiteResourcesPackageKey($packageKey);
        if ($site === null) {
            $this->outputLine('Import site "%s" ...', [$packageKey]);
            $this->siteImportService->importFromPackage($packageKey);
        }

        $user = $this->userService->getUser($username);
        if ($user === null) {
            $this->outputLine('Create user "%s" ...', [$username]);
            $this->userService->createUser($username, $password, 'John', 'Doe', ['Neos.Neos:Administrator']);
        }
    }
}
