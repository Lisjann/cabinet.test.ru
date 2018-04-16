<?php

namespace Top10\CabinetBundle\Service;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Bridge\Monolog\Logger;

use Top10\CabinetBundle\Classes\JsonImportException;

class JsonImport
{
	protected $kernel;
	protected $container;
	protected $logger;

	protected $file;
	protected $jsondir; 

	/**
	 * Нужно для тестов, для подкидывания липового файла
	 */
	public function setFile($file)
	{
	    $this->file = $file;
	}
	
	public function getFile()
	{
	    return $this->file;
	}

    public function getFilePath()
    {
        return $this->getJsondir() . $this->getFile();
    }
	
	public function setJsondir($dir)
	{
	    $this->jsondir = $dir;
	}
	
	public function getJsondir()
	{
	    return $this->jsondir;
	}

	public function __construct(Kernel $kernel, Logger $logger, $file = "", $jsonDir = "var/")
	{
		$this->kernel = $kernel;
		$this->logger = $logger;
		$this->container = $kernel->getContainer();
		$this->jsondir = $jsonDir;
		$this->file = $file;
	}

    /**
     * @param $string
     * @param mixed|null $error
     * @return mixed|null
     */
    public function jsonValidate( $string, &$error = null, $assoc = false )
    {
        // отрезаем BOM
        $bom = mb_substr($string, 0, 3);
        if ($bom === "\xEF\xBB\xBF") {
            $string = mb_substr($string, 3);
        }

        $json = json_decode($string, $assoc);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = false;
                break;
            case JSON_ERROR_DEPTH:
                $error =  'Достигнута максимальная глубина стека';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error =  'Некорректные разряды или не совпадение режимов';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error =  'Некорректный управляющий символ';
                break;
            case JSON_ERROR_SYNTAX:
                $error =  'Синтаксическая ошибка, некорректный JSON';
                break;
            case JSON_ERROR_UTF8:
                $error =  'Некорректные символы UTF-8, возможно неверная кодировка';
                break;
            default:
                $error =  'Неизвестная ошибка';
                break;
        }

        return $error ? null: $json;
    }

	    /**
     * @param $string
     * @param mixed|null $error
     * @return mixed|null
     */
    public function CSVValidate($string)
    {
		$row = 0;
		$CSV = array();
		// отрезаем BOM
		//$bom = mb_substr($string, 0, 3);
		//if ($bom === "\xEF\xBB\xBF") {
		//	$string = mb_substr($string, 3);
		//}

		while (($data = fgetcsv( $string, 1000, ";")) !== FALSE) {
			$num = count($data);
			$row++;

			$CSV[$row] = (object) array(
				'Article' => $data[0],//Код товара внутренний
				'GPmater' => $data[1],//Код подразделения
				'Name' => $data[2],// Имя
				'unit' => $data[3],// Еденицы измерения
				'Price' => $data[4],// Цена Опт3+20%
				'Price2' => $data[5],// Цена Опт3+20% со сидкой
				'Quantity' => $data[6],// Количество
				'WidthDisk' => $data[7],// Ширина диска
				'RadiusDisk' => $data[8],// Диаметр диска
				'Numberfixtures' => $data[9],// Количество креплений
				'Wheelbase' => $data[10],// Межосевое растояние
				'Boom' => $data[11],// Вылет
				'Centralhole' => $data[12],// Центральное отверстие
				'Material' => $data[13],// Материал
				'Season' => $data[14],// Сезон
				'WidthTire' => $data[15],// Ширина Шины
				'Height' => $data[16],// Профиль
				'RadiusTire' => $data[17],// Диаметр Шины
				'Maxload' => $data[18],// Макс Вес
				'Maxspeed' => $data[19],// Макс Скорость
				'Camera' => $data[20],// Камера
				'Model' => $data[21],// Модель
				'Codemodel' => $data[22],// код Модели
				'ArticleExternal' => $data[23],// код неопределен
				'Brand' => $data[24],// Брэнд
				'Quantityres' => $data[25],// Количество физически
				'Pricerecomend' => $data[26],// Цена рекамендованная производителем
				'PriceVIP' => $data[27],// Цена рекамендованная производителем
			);
			/*$CSV[$row][Article] = $data[0];//Код товара внутренний
			$CSV[$row][GPmater] = $data[1];//Код подразделения
			$CSV[$row][Name] = $data[2];// Имя
			$CSV[$row][unit] = $data[3];// Еденицы измерения
			$CSV[$row][Price] = $data[4];// Цена Опт3+20%
			$CSV[$row][Price2] = $data[5];// Цена Опт3+20% со сидкой
			$CSV[$row][Quantity] = $data[6];// Количество
			$CSV[$row][WidthDisk] = $data[7];// Ширина диска
			$CSV[$row][RadiusDisk] = $data[8];// Диаметр диска
			$CSV[$row][Numberfixtures] = $data[9];// Количество креплений
			$CSV[$row][Wheelbase] = $data[10];// Межосевое растояние
			$CSV[$row][Boom] = $data[11];// Вылет
			$CSV[$row][Centralhole] = $data[12];// Центральное отверстие
			$CSV[$row][Material] = $data[13];// Материал
			$CSV[$row][Season] = $data[14];// Сезон
			$CSV[$row][WidthTire] = $data[15];// Ширина Шины
			$CSV[$row][Profile] = $data[16];// Профиль
			$CSV[$row][RadiusTire] = $data[17];// Диаметр Шины
			$CSV[$row][Maxload] = $data[18];// Макс Вес
			$CSV[$row][Maxspeed] = $data[19];// Макс Скорость
			$CSV[$row][Camera] = $data[20];// Камера
			$CSV[$row][Model] = $data[21];// Модель
			$CSV[$row][Codemodel] = $data[22];// код Модели
			$CSV[$row][Codeun] = $data[23];// код неопределен
			$CSV[$row][Brand] = $data[24];// Брэнд
			$CSV[$row][Quantityrez] = $data[25];// Количество физически
			$CSV[$row][Pricerecomend] = $data[26];// Цена рекамендованная производителем*/
		}
		return $CSV;
    }


    /**
     * Отравка стандартного сообщения
     *
     * @param array $messages
     * @param $file
     * @return bool
     */
    public function sendEmail(array $messages, $file, $attachment = false)
    {
    	$kernel = $this->kernel;
    	$container = $kernel->getContainer();
        $env = $kernel->getEnvironment();
    	
    	// Отправляем email с уведомлением - "были ошибки" или "все хорошо"
        $emails = $container->getParameter('top10_cabinet.emails');

        $templating = $container->get('templating');
    	$path_parts = pathinfo($file);
    	$filename = $path_parts['basename'];

        $body = $templating->render('Top10CabinetBundle:Mail:InfoMail.html.twig', array(
            'errors' => $messages,
            'filename' => $filename)
        );

    	/* @var $message \Swift_Mime_Message */
    	$message = \Swift_Message::newInstance()
    		->setSubject('Информация по обновлению: '.$filename)
    		->setContentType("text/html")
    		->setFrom($container->getParameter('top10_cabinet.emails.default'))
    		->setTo($emails)
    		->setBody($body);

        if ($attachment)
            $message->attach(\Swift_Attachment::fromPath($attachment));

    	$container->get('mailer')->send($message);

    	return true;
    }

    /**
     * handler ошибок в случае провала обработки файла
     */
    public function handleException(\Exception $e, $filePath)
    {
        $env = $this->kernel->getEnvironment();

        // делаем резервную копию файла, добавляя метку времени к названию файла,
        // также сохранять отчет об ошибке с таким же именем, но расширением *.log
        // файлы сохранять в ту же папку, где происходит обработка
        $fInfo = pathinfo($filePath);

        $ext = $fInfo['extension'];
        $name = $fInfo['filename'];
        $dir = $fInfo['dirname'];
        $now = new \DateTime('now');
        $newName = sprintf('%s_%s', $name, $now->format('Y-m-d_H-i-s'));
        $newNameFull = sprintf('%s.%s', $newName, $ext);
        $newPath = sprintf('%s/%s', $dir, $newNameFull);
        $logName = sprintf('%s.%s', $newName, 'log');
        $logPath = sprintf('%s/%s', $dir, $logName);

        if( $env == 'prod' ) {
            // Поскольку до удаления файла уже не дойдет, его можно переместить
            rename($filePath, $newPath);
            file_put_contents( $logPath, $e->getMessage() );
        }

        $this->logger->err( $e->getMessage() );

        // в письмо с отчетом после системных сообщений добавить приписку
        // "резервная копия файла сохранена с именем ...
        // отчет об ошибке сохранен с именем ...
        // файл и отчет можно посмотреть на FTP в каталоге обмена"
        $messages = array();
        $messages['error'] = $e->getMessage();
        $messages[] = sprintf(
            'резервная копия файла сохранена с именем %s;
            отчет об ошибке сохранен с именем %s;
            файл и отчет можно посмотреть на FTP в каталоге обмена',
            $newNameFull, $logName
        );

        $this->sendEmail( $messages, $filePath );
    }

    /**
     * handler в случае успеха обработки файла
     *
     * @param array $messages
     * @param $filePath
     */
    public function handleSuccess(array $messages, $filePath)
    {
        $this->sendEmail($messages, $filePath);
        if( !empty($messages) ) {
            $msg = sprintf('[%s]: %s', $filePath, implode('; ', $messages));
            $this->logger->info($msg);
        }
        else {
            $msg = sprintf('[%s]: Ошибок не обнаружено.', $filePath);
            $this->logger->info($msg);
        }
    }

}