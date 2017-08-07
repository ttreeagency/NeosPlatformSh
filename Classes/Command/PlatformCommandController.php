<?php
declare(strict_types=1);

namespace Ttree\NeosPlatformSh\Command;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\PackageManager;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\ResourceManagement\ResourceManager;
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
     * @var ResourceManager
     * @Flow\Inject
     */
    protected $resourceManager;

    /**
     * @var PackageManager
     * @Flow\Inject
     */
    protected $packageManager;

    /**
     * @param string $username
     * @param string $password
     * @Flow\Internal
     */
    public function createAdminAccountCommand(string $username, string $password)
    {
        $this->outputLine('+ <comment>create user %s</comment>', [$username]);

        $user = $this->userService->getUser($username);
        if ($user === null) {
            $this->outputLine('Create user "%s" ...', [$username]);
            $this->userService->createUser($username, $password, 'John', 'Doe', ['Neos.Neos:Administrator']);
        }
    }

    /**
     * @Flow\Internal
     * @param string $package
     */
    public function importSitePackageCommand(string $package)
    {
        if (!$this->packageManager->isPackageActive($package)) {
            $this->outputLine('+ <comment>skip import site package %s, missing package</comment>', [$package]);
            return;
        }
        $this->outputLine('+ <comment>import site package %s</comment>', [$package]);

        $site = $this->siteRepository->findOneBySiteResourcesPackageKey($package);
        if ($site === null) {
            $this->outputLine('Import site "%s" ...', [$package]);
            $this->siteImportService->importFromPackage($package);
        }
    }

    /**
     * @Flow\Internal
     */
    public function publishStaticResourcesCommand()
    {
        $this->outputLine('+ <comment>publish static resources</comment>');

        $collection = $this->resourceManager->getCollection('static');

        $target = $collection->getTarget();
        $target->publishCollection($collection, function ($iteration) {
            $this->clearState($iteration);
        });
    }
}
