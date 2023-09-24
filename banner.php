<?php

const DB_NAME = 'test';
const DB_USER = 'root';
const DB_PASSWORD = '';
const DB_HOST = 'localhost';
const DB_TABLE = '';
const USER_AGENT = 'User-Agent';


$db = new DB(DB_NAME, DB_USER, DB_PASSWORD, DB_HOST, DB_TABLE);
$ret = $db->connect();

if ($ret && $headers = getallheaders()) {

    $activeConnect = $db->activeConnect;
    $userAgent = null;

     foreach ($headers as $name => $header) {
         if ($name === USER_AGENT) {
             $userAgent = $header;
             break;
         }
     }

    $ip = getId();
    $url = $_SERVER['HTTP_REFERER'];
    $viewDate = date("Y-m-d H:i:s");
    $visitor = new Visitor($ip, $userAgent, $viewDate, $url, $activeConnect);
    $visitor->visit();
     
    $image = new Image ('Test Image');
    $image->createImage();
 }

function getId(): string
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    return $_SERVER['REMOTE_ADDR'];
}

class Image
{
    public function __construct(private readonly string $imageName)
    {}

    public function createImage(): void
    {
        # Create a blank image and add some text
        $im = imagecreatetruecolor(120, 120);
        $textColor = imagecolorallocate($im, 233, 14, 91);
        imagestring($im, 1, 5, 5, $this->imageName, $textColor);

        #Set the content type header - in this case image/jpeg
        header('Content-Type: image/jpeg');

        # Output the image
        imagejpeg($im);

        # Free up memory
        imagedestroy($im);
    }
}

class DB
{
    public $activeConnect;

    public function __construct(
        private readonly string $dbName,
        private readonly string $dbUser,
        private readonly string $dbPass,
        private readonly string $dbHost,
        public string           $table
    )
    {}

    public function connect(): bool
    {
        try {
            $this->activeConnect = new mysqli($this->dbHost, $this->dbUser, $this->dbPass, $this->dbName);

            return true;
        } catch (\Error $exception) {
            printf("Connection failed: %s\ ", mysqli_connect_error());

            return false;
        }
    }
}

class Visitor
{
    private int $count = 0;

    public function __construct(
        protected string    $ip,
        private string|null $userAgent,
        private string      $viewDate,
        private string      $url,
        private mixed       $activeConnect)
    {}

    public function visit(): bool
    {
        $visitorExist = $this->checkIfVisitorExist($this->ip, $this->userAgent, $this->url);

        if (!is_array($visitorExist)) {
            return $this->createNewVisitor($this->ip, $this->userAgent, $this->url, $this->viewDate);
        }

        return $this->updateVisitor((int)$visitorExist['id'], (int)$visitorExist['views_count'], $this->viewDate);
    }


    private function checkIfVisitorExist(string $ip, mixed $userAgent, string $url): array|bool
    {
        $sql = "SELECT * FROM visitors WHERE ip_address=? AND user_agent=? AND page_url=? LIMIT 1";

        $stmt = $this->activeConnect->prepare($sql);
        $stmt->bind_param("sss", $ip, $userAgent, $url);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return false;
    }


    private function createNewVisitor(string $ip, string $userAgent, string $url, string $viewDate): bool
    {
        $sql = "INSERT INTO visitors (ip_address, user_agent, view_date, page_url, views_count) VALUES (?, ?, ?, ?, '1')";

        $stmt = $this->activeConnect->prepare($sql);
        $stmt->bind_param("ssss", $ip, $userAgent, $viewDate, $url);
        $ret = $stmt->execute();
        $stmt->close();

        if ($ret === true) {
            return true;
        }
        echo('Error save date');

        return false;
    }

    private function updateVisitor(string $id, int $lastCount, string $viewDate): bool
    {
        $newCount = $lastCount + 1;

        $sql = "UPDATE visitors SET views_count=?, view_date=? WHERE id=?";

        $stmt = $this->activeConnect->prepare($sql);
        $stmt->bind_param("iss", $newCount, $viewDate, $id);
        $ret = $stmt->execute();
        $stmt->close();

        if ($ret === true) {
            return true;
        }

        echo('Error save date');

        return false;
    }
}