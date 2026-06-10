<?php
/**
 * @file plugins/generic/webchatNagastra/WebchatNagastraPlugin.php
 *
 * Plugin Webchat Nagastra untuk OJS 3.5.x
 */

namespace APP\plugins\generic\webchatNagastra;

use APP\core\Application;
use APP\core\Request;
use APP\template\TemplateManager;
use PKP\core\JSONMessage;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class WebchatNagastraPlugin extends GenericPlugin
{
    public const SETTING_ENABLED = 'webchatEnabled';
    public const SETTING_SCRIPT_URL = 'scriptUrl';
    public const SETTING_POSITION = 'position';

    /**
     * Register plugin.
     */
    public function register($category, $path, $mainContextId = null): bool
    {
        $success = parent::register($category, $path, $mainContextId);

        if ($success && $this->getEnabled()) {
            Hook::add('TemplateManager::display', $this->addWebchatScript(...));
        }

        return $success;
    }

    /**
     * Nama yang tampil di daftar plugin.
     */
    public function getDisplayName(): string
    {
        return __('plugins.generic.webchatNagastra.displayName');
    }

    /**
     * Deskripsi plugin.
     */
    public function getDescription(): string
    {
        return __('plugins.generic.webchatNagastra.description');
    }

    /**
     * Tambahkan tombol Settings di daftar plugin.
     */
    public function getActions($request, $actionArgs): array
    {
        $actions = parent::getActions($request, $actionArgs);

        $router = $request->getRouter();
        $actions[] = new LinkAction(
            'settings',
            new AjaxModal(
                $router->url(
                    $request,
                    null,
                    null,
                    'manage',
                    null,
                    [
                        'verb' => 'settings',
                        'plugin' => $this->getName(),
                        'category' => 'generic',
                    ]
                ),
                __('plugins.generic.webchatNagastra.manager.settings')
            ),
            __('manager.plugins.settings')
        );

        return $actions;
    }

    /**
     * Tampilkan/simpan form setting.
     */
    public function manage($args, $request): JSONMessage
    {
        $verb = $request->getUserVar('verb');

        if ($verb !== 'settings') {
            return parent::manage($args, $request);
        }

        $context = $request->getContext();
        $contextId = $context ? $context->getId() : CONTEXT_ID_NONE;
        $templateMgr = TemplateManager::getManager($request);

        if ($request->getUserVar('save')) {
            $enabled = (bool) $request->getUserVar(self::SETTING_ENABLED);
            $scriptUrl = trim((string) $request->getUserVar(self::SETTING_SCRIPT_URL));
            $position = trim((string) $request->getUserVar(self::SETTING_POSITION));

            if (!in_array($position, ['frontend', 'all'], true)) {
                $position = 'frontend';
            }

            if ($enabled && !$this->isValidScriptUrl($scriptUrl)) {
                return new JSONMessage(false, __('plugins.generic.webchatNagastra.error.invalidUrl'));
            }

            $this->updateSetting($contextId, self::SETTING_ENABLED, $enabled ? '1' : '0', 'string');
            $this->updateSetting($contextId, self::SETTING_SCRIPT_URL, $scriptUrl, 'string');
            $this->updateSetting($contextId, self::SETTING_POSITION, $position, 'string');

            return new JSONMessage(true);
        }

        $templateMgr->assign([
            'pluginName' => $this->getName(),
            'webchatEnabled' => $this->getSetting($contextId, self::SETTING_ENABLED) === '1',
            'scriptUrl' => (string) $this->getSetting($contextId, self::SETTING_SCRIPT_URL),
            'position' => $this->getSetting($contextId, self::SETTING_POSITION) ?: 'frontend',
        ]);

        return new JSONMessage(true, $templateMgr->fetch($this->getTemplateResource('settings.tpl')));
    }

    /**
     * Sisipkan script webchat ke halaman OJS.
     */
    public function addWebchatScript(string $hookName, array $args): bool
    {
        /** @var TemplateManager $templateMgr */
        $templateMgr = $args[0];
        $request = Application::get()->getRequest();
        $context = $request->getContext();
        $contextId = $context ? $context->getId() : CONTEXT_ID_NONE;

        $enabled = $this->getSetting($contextId, self::SETTING_ENABLED) === '1';
        $scriptUrl = trim((string) $this->getSetting($contextId, self::SETTING_SCRIPT_URL));
        $position = $this->getSetting($contextId, self::SETTING_POSITION) ?: 'frontend';

        if (!$enabled || !$this->isValidScriptUrl($scriptUrl)) {
            return false;
        }

        if ($position === 'frontend' && !$this->isFrontendRequest($request)) {
            return false;
        }

        $contexts = ['frontend'];
        if ($position === 'all') {
            $contexts[] = 'backend';
        }

        // OJS/PKP akan membuat tag <script src="..."></script> dari URL ini.
        $templateMgr->addJavaScript(
            'webchat-nagastra',
            $scriptUrl,
            [
                'contexts' => $contexts,
            ]
        );

        return false;
    }

    /**
     * Validasi URL script.
     */
    private function isValidScriptUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $path = strtolower($parts['path'] ?? '');

        if (!in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        // Lebih aman jika link berupa file JavaScript.
        // Tetap izinkan URL tanpa .js karena beberapa widget memakai URL dinamis.
        if ($path !== '' && str_contains($path, '<')) {
            return false;
        }

        return true;
    }

    /**
     * Hindari muncul di halaman manajemen/admin jika pilihan hanya frontend.
     */
    private function isFrontendRequest(Request $request): bool
    {
        $page = (string) $request->getRequestedPage();

        return !in_array($page, [
            'admin',
            'management',
            'dashboard',
            'submission',
            'workflow',
            'stats',
            'api',
            '$$$call$$$',
        ], true);
    }
}

// Kompatibilitas untuk mode non-strict.
if (!PKP_STRICT_MODE) {
    class_alias('APP\\plugins\\generic\\webchatNagastra\\WebchatNagastraPlugin', 'WebchatNagastraPlugin');
}
