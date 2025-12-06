<?php
/**
 * PHPUnit bootstrap file for Nextcloud Verein app
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Mock OCP classes for unit testing without full Nextcloud environment
if (!interface_exists('OCP\IConfig')) {
    interface IConfig {
        public function getAppValue(string $appName, string $key, string $default = ''): string;
        public function setAppValue(string $appName, string $key, string $value): void;
        public function deleteAppValue(string $appName, string $key): void;
    }
    class_alias('IConfig', 'OCP\IConfig');
}

if (!interface_exists('OCP\IRequest')) {
    interface IRequest {
        public function getParam(string $key, $default = null);
    }
    class_alias('IRequest', 'OCP\IRequest');
}

if (!interface_exists('OCP\Mail\IMailer')) {
    interface IMailer {
        public function send($message): void;
        public function createMessage();
    }
    class_alias('IMailer', 'OCP\Mail\IMailer');
}

if (!class_exists('OCP\AppFramework\Http\JSONResponse')) {
    class JSONResponse {
        private $data;
        private $status;
        public function __construct($data = [], int $status = 200) {
            $this->data = $data;
            $this->status = $status;
        }
        public function getData() { return $this->data; }
        public function getStatus() { return $this->status; }
    }
    class_alias('JSONResponse', 'OCP\AppFramework\Http\JSONResponse');
}

if (!class_exists('OCP\AppFramework\Http\DataDownloadResponse')) {
    class DataDownloadResponse {
        private $data;
        private $filename;
        private $contentType;
        public function __construct($data, $filename, $contentType) {
            $this->data = $data;
            $this->filename = $filename;
            $this->contentType = $contentType;
        }
        public function getData() { return $this->data; }
    }
    class_alias('DataDownloadResponse', 'OCP\AppFramework\Http\DataDownloadResponse');
}

if (!class_exists('OCP\AppFramework\Http')) {
    class Http {
        public const STATUS_OK = 200;
        public const STATUS_BAD_REQUEST = 400;
        public const STATUS_FORBIDDEN = 403;
        public const STATUS_INTERNAL_SERVER_ERROR = 500;
    }
    class_alias('Http', 'OCP\AppFramework\Http');
}

if (!class_exists('OCP\AppFramework\Db\DoesNotExistException')) {
    class DoesNotExistException extends \Exception {}
    class_alias('DoesNotExistException', 'OCP\AppFramework\Db\DoesNotExistException');
}

// Mock QBMapper for Mapper classes
if (!class_exists('OCP\AppFramework\Db\QBMapper')) {
    abstract class QBMapper {
        public function __construct($db = null, $tableName = '', $entityClass = '') {}
        abstract public function find(int $id);
        public function insert($entity) { return $entity; }
        public function update($entity) { return $entity; }
        public function delete($entity) { return $entity; }
    }
    class_alias('QBMapper', 'OCP\AppFramework\Db\QBMapper');
}

if (!class_exists('OCP\AppFramework\Db\Entity')) {
    abstract class Entity {
        protected $id;
        public function getId() { return $this->id; }
        public function setId($id) { $this->id = $id; }
        public function jsonSerialize(): array { return ['id' => $this->id]; }
    }
    class_alias('Entity', 'OCP\AppFramework\Db\Entity');
}

if (!interface_exists('OCP\IUserSession')) {
    interface IUserSession {
        public function getUser();
        public function isLoggedIn(): bool;
    }
    class_alias('IUserSession', 'OCP\IUserSession');
}

if (!interface_exists('OCP\IUser')) {
    interface IUser {
        public function getUID(): string;
        public function getDisplayName(): string;
    }
    class_alias('IUser', 'OCP\IUser');
}

if (!interface_exists('OCP\IDBConnection')) {
    interface IDBConnection {
        public function getQueryBuilder();
    }
    class_alias('IDBConnection', 'OCP\IDBConnection');
}

if (!interface_exists('OCP\AppFramework\Utility\ITimeFactory')) {
    interface ITimeFactory {
        public function getTime(): int;
        public function getDateTime(string $time = 'now', \DateTimeZone $timezone = null): \DateTime;
    }
    class_alias('ITimeFactory', 'OCP\AppFramework\Utility\ITimeFactory');
}

if (!class_exists('OCP\BackgroundJob\TimedJob')) {
    abstract class TimedJob {
        protected int $interval = 0;
        protected $time;
        
        public const TIME_SENSITIVE = 0;
        public const TIME_INSENSITIVE = 1;
        
        public function __construct($time) {
            $this->time = $time;
        }
        
        protected function setInterval(int $seconds): void {
            $this->interval = $seconds;
        }
        
        protected function setTimeSensitivity(int $sensitivity): void {
            // Implementation for testing
        }
        
        abstract protected function run($argument): void;
    }
    class_alias('TimedJob', 'OCP\BackgroundJob\TimedJob');
}

if (!interface_exists('OCP\Mail\IEMailTemplate')) {
    interface IEMailTemplate {
        public function setSubject(string $subject): void;
        public function addHeading(string $heading, string $plainHeading = ''): void;
        public function addBodyText(string $text, string $plainText = ''): void;
        public function addFooter(string $text = '', string $lang = ''): void;
    }
    class_alias('IEMailTemplate', 'OCP\Mail\IEMailTemplate');
}
