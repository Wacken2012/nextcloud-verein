<?php
namespace OCA\Verein\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class AdminSection implements IIconSection {
    private IL10N $l;
    private IURLGenerator $url;

    public function __construct(IURLGenerator $url, IL10N $l) {
        $this->url = $url;
        $this->l = $l;
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
