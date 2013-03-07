<?php

/**
 * This file is part of the DVO package.
 *
 * (c) Bobby DeVeaux <me@bobbyjason.co.uk> / t: @bobbyjason
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace DVO\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DVO\AMQP\Broker\BrokerFactory;

class IcodesController
{
    protected $brokerFactory;


    /**
     * A cURL function.
     *
     * @param string $url The url to download.
     *
     * @return string
     */
    public function getData($url)
    {
        $ch      = curl_init();
        $timeout = 15;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    /**
     * VoucherController constructor.
     *
     * @param BrokerFactory $brokerFactory The BrokerFactory factory.
     */
    public function __construct(BrokerFactory $brokerFactory)
    {
        $this->brokerFactory = $brokerFactory;
    }

    /**
     * Handles the HTTP GET.
     *
     * @param Request     $request The request.
     * @param Application $app     The app.
     *
     * @return JsonResponse
     */
    public function download(Request $request, Application $app)
    {
        $broker = $this->brokerFactory->create('voucher');

        // list of iCodes merchants
        $total    = 0;
        $download = false;
        $filename = __DIR__ . '/../../../files/icodes48hr.xml';
        $xml      = $this->downloadFile($filename);

        $pages = (int) $xml->TotalPages;

        for ($i = 1; $i < $pages; $i++) {
            foreach ($xml->item as $item) {
                $arr = array(
                    'title'             => (string) $item->title,
                    'description'       => (string) $item->description,
                    'merchant'          => (string) $item->merchant,
                    'merchant_logo_url' => (string) $item->merchant_logo_url,
                    'voucher_code'      => (string) $item->voucher_code,
                    'merchant'          => (string) $item->merchant,
                    'start_date'        => (string) $item->start_date,
                    'expiry_date'       => (string) $item->expiry_date,
                    'deep_link'         => (string) $item->deep_link,
                    'merchant_url'      => (string) $item->merchant_url,
                    'category_id'       => (string) $item->category_id,
                    'category'          => (string) $item->category,
                    'deep_link'         => (string) $item->deep_link,
                );

                if (true === empty($arr['deep_link'])) {
                    $arr['deep_link'] = $arr['merchant_url'];
                }

                $message = json_encode($arr);

                // blah send as JSON array
                $broker->sendMessage($message, $broker->getKey());

                //usleep(200000);
            }

            $xml = $this->downloadFile($filename, $i+1);
        }

        $total += $xml->Results;

        return 'Total: ' . $total . PHP_EOL;
    }

    /**
     * Download the file.
     *
     * @param string  $filename The filename.
     * @param integer $page     The page number to download.
     *
     * @return string
     */
    public function downloadFile($filename, $page = 1)
    {
        $filename = $filename . '.' . $page;
        if (false === file_exists($filename) || filemtime($filename) < (time()-3600)) {
            $url  = 'http://webservices.icodes.co.uk/ws2.php?UserName=icodes&SubscriptionID=';
            $url .= '92262bf907af914b95a0fc33c3f33bf6&RequestType=Codes&Action=New&Hours=48&PageSize=20&Page=' . $page;
            $data = $this->getData($url);
            $file = fopen($filename, 'w+');
            fwrite($file, $data);
            fclose($file);
        }

        return simplexml_load_file($filename);
    }
}
