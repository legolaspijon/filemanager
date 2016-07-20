<?php


class Manager
{
    private static $_instance = null;

    private $catalogPath;
    public $sort = 'n';
    public $order = 'a';

    private function __construct()
    {
        $this->setSort();
        $this->setOrder();
        $this->setCatalog();
    }

    /**
     * Устанавливаем каталог из GET, если GET - empty, то по умолчанию
     */
    private function setCatalog()
    {
        if (isset($_GET['dir']) && !empty($_GET['dir'])) {
            $this->catalogPath = realpath($_GET['dir']);
        } else {
            $this->catalogPath = $this->getDefaultCatalog();
        }
    }

    public function setSort()
    {
        if (isset($_GET['s'])) {
            $this->sort = $_GET['s'];
            setcookie('s', $_GET['s']);
        }
    }

    public function setOrder()
    {
        if (isset($_GET['o'])) {
            $this->order = $_GET['o'];
        }
    }

    /**
     * Возвращаем массив файлов из каталога
     * */
    public function getCatalogFiles()
    {

        if (is_dir($this->catalogPath)) {
            $dirs = [];
            $files = [];

            $d = opendir($this->catalogPath);

            while (false != ($file = readdir($d))) {
                $file = iconv('cp1251', 'utf-8', $file);
                if ($file == '.' || $file == '..') continue;

                if (is_file($this->catalogPath . '/' . $file)) {
                    $info = pathinfo($file);
                    $files[$file] = [
                        'type' => 'f',
                        'size' => filesize($this->catalogPath . '/' . $file),
                        'date_create' => filemtime($this->catalogPath . '/' . $file),
                        'fileType' => $info['extension'],
                    ];
                }

                if (is_dir($this->catalogPath . '/' . $file)) {
                    $dirs[$file] = [
                        'type' => 'd',
                        'date_create' => filemtime($this->catalogPath . '/' . $file),
                        'path' => $this->catalogPath . '/' . $file,
                        'fileType' => filetype($this->catalogPath . '/' . $file),
                    ];
                }
            }

            $files = array_merge($dirs, $files);

            switch ($this->sort) {
                case 's':
                    $files = $this->sortBySize($files);
                    break;

                case 't':
                    $files = $this->sortByType($files);
                    break;

                case 'n':
                    $files = $this->sortByName($files);
                    break;
            }
            return $files;
        }
        return false;
    }

    /**
     * Возвращаем каталог по умолчанию
     * */
    public function getDefaultCatalog()
    {
        return realpath('.');
    }

    /**
     * Выводим список каталогов
     * */
    public function displayCatalog()
    {
        $files = $this->getCatalogFiles();
        $table = "<table class='table table-striped'>";
        $tr = '';

        if ($_COOKIE['s'] == $this->sort) {
            $order = $this->order == 'd' ? 'a' : 'd';
        } else {
            $order = 'a';
        }

        $table .= "<tr>
                    <th><a href='/?dir={$this->catalogPath}&s=n&o={$order}'>Name</a></th>
                    <th><a href='/?dir={$this->catalogPath}&s=t&o={$order}'>File type</a></th>
                    <th><a href='/?dir={$this->catalogPath}&s=s&o={$order}'>File size</a></th>
                    <th>last changed</th>
                    </tr>
                    <tr>
                        <td colspan='4'><a href='/?dir={$this->catalogPath}/..'><span class='glyphicon glyphicon-arrow-left'></span>&nbspBACK</a></td>
                    </tr>";
        foreach ($files as $filename => $fileParam) {
            $filePath = $this->catalogPath . '/' . $filename;
            $date = ($filename == '..') ? '' : date("d.m.Y", $fileParam['date_create']);

            $tr .= "\n<tr>";
            if ($fileParam['type'] == 'd') {
                $tr .= "<td><span class='glyphicon glyphicon-folder-open' aria-hidden='true'></span>&nbsp<a href='/?dir={$fileParam['path']}'>$filename</td><td>{$fileParam['fileType']}</td><td></td><td>$date</td>";
            }

            if ($fileParam['type'] == 'f') {
                $tr .= "<td><span class='glyphicon glyphicon-list-alt' aria-hidden='true'></span>&nbsp$filename</td><td>{$fileParam['fileType']}</td><td>{$fileParam['size']}</td><td>$date</td>";
            }
            $tr .= "</tr>\n";
        }

        $table .= $tr . "\n</table>";

        echo $table;
    }


    /**
     * Возвращаем екземпляр
     * */
    public static function getManager()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function sortByName($files)
    {
        if ($this->order == 'a') {
            ksort($files);
        } elseif ($this->order == 'd') {
            ksort($files);
            $files = array_reverse($files);
        }
        return $files;
    }


    public function sortByType($files)
    {
        foreach ($files as $file) {
            $fieldsSort[] = $file['fileType'];
        }

        if ($this->order == 'a') {
            array_multisort($fieldsSort, SORT_ASC, $files);
        } elseif ($this->order == 'd') {
            array_multisort($fieldsSort, SORT_DESC, $files);
            $files = array_reverse($files);
        }

        return $files;
    }

    public function sortBySize($files)
    {
        foreach ($files as $file) {
            $fieldsSort[] = $file['size'];
        }

        if ($this->order == 'a') {
            array_multisort($fieldsSort, SORT_ASC, $files);
        } elseif ($this->order == 'd') {
            array_multisort($fieldsSort, SORT_DESC, $files);
        }

        return $files;
    }

    protected function __clone()
    {
    }
}