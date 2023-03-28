<?php
namespace Tonder\Payment\Gateway\Http\Converter;

use Magento\Framework\Xml\Parser as XmlParser;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Psr\Log\LoggerInterface;

/**
 * Class XmlToArray
 *
 * @package Tonder\Payment\Gateway\Http\Converter
 */
class XmlToArray implements ConverterInterface
{

    /**
     * @var XmlParser
     */
    private $parser;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * XmlToArray constructor.
     *
     * @param XmlParser $parser
     * @param LoggerInterface $logger
     */
    public function __construct(
        XmlParser $parser,
        LoggerInterface $logger
    ) {
        $this->parser = $parser;
    }

    /**
     * Converts gateway response to ENV structure
     *
     * @param mixed $response
     * @return array
     * @throws ConverterException
     */
    public function convert($response)
    {
        try {
            $this->parser->loadXML($response);
        } catch (\Exception $e) {
            throw new ConverterException(__('Can\'t read response from Moneris'));
        }
        $result = $this->parser->xmlToArray();
        if (!empty($result['response']['receipt'])) {
            return $result['response']['receipt'];
        } elseif (!empty($result['Mpi2Response']['receipt'])) {
            return $result['Mpi2Response']['receipt'];
        } elseif (!empty($result['Mpi2Response'])) {
            return $result['Mpi2Response'];
        } elseif (!empty($result['response'])) {
            return $result['response'];
        } else {
            $this->logger->debug('Can\'t read response from Moneris');
            throw new ConverterException(__('Can\'t read response from Moneris'));
        }
    }
}
