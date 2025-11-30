<?php
namespace OCA\Verein\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;
use OCP\AppFramework\IAppContainer;

class AdminSection implements IIconSection {
    private IL10N $l;
    private IURLGenerator $url;

    public function __construct(IAppContainer $container) {
        $this->url = $container->query(IURLGenerator::class);
        $this->l = $container->query(IL10N::class);
    }

    /**
     * Returns the relative path to an 16*16 icon describing the section
     */
    public function getIcon(): string {
        return $this->url->imagePath('verein', 'app.svg');
    }

    /**
     * Returns the ID of the section
     */
    public function getID(): string {
        return 'verein';
    }

    /**
     * Returns the translated name as it should be displayed
     */
    public function getName(): string {
        return $this->l->t('Verein');
    }

    /**
     * Priority for ordering in settings
     */
    public function getPriority(): int {
        return 50;
    }
}
