<?php
declare(strict_types=1);

namespace Ttree\NeosPlatformSh\Command;

use Neos\Flow\Annotations as Flow;
use Ttree\FlowPlatformSh\Annotations as PlatformSh;
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
     * @Flow\Internal
     * @PlatformSh\DeployHook
     */
    public function createAdminAccountCommand()
    {
        $username = 'admin';
        $password = 'changeme';
        $this->outputLine('+ <comment>create user %s</comment>', [$username]);

        $user = $this->userService->getUser($username);
        if ($user === null) {
            $this->outputLine('Create user "%s" ...', [$username]);
            $this->userService->createUser($username, $password, 'John', 'Doe', ['Neos.Neos:Administrator']);
        }
    }

    /**
     * @Flow\Internal
     * @PlatformSh\DeployHook
     */
    public function importSitePackageCommand()
    {
        $packageKey = 'Neos.Demo';
        $this->outputLine('+ <comment>import site package %s</comment>', [$packageKey]);

        $site = $this->siteRepository->findOneBySiteResourcesPackageKey($packageKey);
        if ($site === null) {
            $this->outputLine('Import site "%s" ...', [$packageKey]);
            $this->siteImportService->importFromPackage($packageKey);
        }
    }

    /**
     * @Flow\Internal
     * @PlatformSh\DeployHook
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
