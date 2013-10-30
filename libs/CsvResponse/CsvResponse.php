<?php

/**
 * CSV download response.
 *
 * @author     Petr 'PePa' Pavel
 *
 * @property-read array  $data
 * @property-read string $name
 * @property-read bool   $addHeading
 * @property-read string $glue
 * @property-read string $contentType
 * @package Nette\Application\Responses
 */
class CsvResponse extends Nette\Object implements Nette\Application\IResponse {

    /** @var array */
    private $data;

    /** @var string */
    private $name;

    /** @var bool */
    public $addHeading;

    /** @var string */
    public $glue;

    /** @var string */
    private $charset;

    /** @var string */
    private $contentType;
    
    private $bomCharacters;

    /**
     * @param  string  data (array of arrays - rows/columns)
     * @param  string  imposed file name
     * @param  bool    return array keys as the first row (column headings)
     * @param  string  glue between columns (comma or a semi-colon)
     * @param  string  MIME content type
     */
    public function __construct($data, $name = NULL, $addHeading = FALSE, $glue = ';', $charset = NULL, $contentType = NULL) {
        // ----------------------------------------------------
        $this->data = $data;
        $this->name = $name;
        $this->addHeading = $addHeading;
        $this->glue = $glue;
        $this->charset = $charset;
        $this->charset = $charset ? $charset : 'UTF-8';
        $this->contentType = $contentType ? $contentType : 'text/comma-separated-values';
        
        // nastavim bom znaky pre utf-8
        $this->bomCharacters = chr(0xEF) . chr(0xBB) . chr(0xBF);
    }

    /**
     * Returns the file name.
     * @return string
     */
    final public function getName() {
        // ----------------------------------------------------
        return $this->name;
    }

    /**
     * Returns the MIME content type of a downloaded content.
     * @return string
     */
    final public function getContentType() {
        return $this->contentType;
    }

    /**
     * Sends response to output.
     * @return void
     */
    public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse) {
        $httpResponse->setContentType($this->contentType, $this->charset);

        if (empty($this->name)) {
            $httpResponse->setHeader('Content-Disposition', 'attachment');
        } else {
            $httpResponse->setHeader('Content-Disposition', 'attachment; filename="' . $this->name . '"');
        }
        $data = $this->bomCharacters .strip_tags($this->formatCsv());
        $httpResponse->setHeader('Content-Length', strlen($data));
        print $data;
    }

    /**
     * formatCsv
     * @return string
     */
    public function formatCsv() {
        // ----------------------------------------------------
        if (empty($this->data)) {
            return '';
        }

        $csv = array();

        if (!is_array($this->data)) {
            $this->data = iterator_to_array($this->data);
        }
        reset($this->data);

        if ($this->addHeading) {
            $labels = array();
            foreach ($this->addHeading as $key) {
                $labels[] = ucwords(str_replace("_", ' ', $key));
            }
            $csv[] = '"' . join('"' . $this->glue . '"', $labels) . '"';
        }
        
        // data
        foreach ($this->data as $row) {
            if (!is_array($row)) {
                $row = iterator_to_array($row);
            }
            foreach ($row as $key => $value) {
                $value = preg_replace('/[\r\n]+/', ' ', $value);  // remove line endings
                $value = str_replace('"', '""', $value);          // escape double quotes
            }
            $csv[] = '"' . join('"' . $this->glue . '"', $row) . '"';
        }

        return join(PHP_EOL, $csv);
    }

}
