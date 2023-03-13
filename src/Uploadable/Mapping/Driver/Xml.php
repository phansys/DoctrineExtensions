<?php

/*
 * This file is part of the Doctrine Behavioral Extensions package.
 * (c) Gediminas Morkevicius <gediminas.morkevicius@gmail.com> http://www.gediminasm.org
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gedmo\Uploadable\Mapping\Driver;

use Gedmo\Mapping\Driver\Xml as BaseXml;
use Gedmo\Uploadable\Mapping\Validator;

/**
 * This is a xml mapping driver for Uploadable
 * behavioral extension. Used for extraction of extended
 * metadata from xml specifically for Uploadable
 * extension.
 *
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author Miha Vrhovnik <miha.vrhovnik@gmail.com>
 *
 * @internal
 */
class Xml extends BaseXml
{
    public function readExtendedMetadata($meta, array &$config)
    {
        /**
         * @var \SimpleXmlElement
         */
        $xml = $this->_getMapping($meta->getName());
        $xmlDoctrine = $xml;
        $xml = $xml->children(self::GEDMO_NAMESPACE_URI);

        if ('entity' === $xmlDoctrine->getName() || 'mapped-superclass' === $xmlDoctrine->getName()) {
            if (isset($xml->uploadable)) {
                $xmlUploadable = $xml->uploadable;
                $config['uploadable'] = true;
                $config['allowOverwrite'] = $this->_isAttributeSet($xmlUploadable, 'allow-overwrite') ?
                    $this->_getBooleanAttribute($xmlUploadable, 'allow-overwrite') : false;
                $config['appendNumber'] = $this->_isAttributeSet($xmlUploadable, 'append-number') ?
                    $this->_getBooleanAttribute($xmlUploadable, 'append-number') : false;
                $config['path'] = $this->_isAttributeSet($xmlUploadable, 'path') ?
                    $this->_getAttribute($xml->{'uploadable'}, 'path') : null;
                $config['pathMethod'] = $this->_isAttributeSet($xmlUploadable, 'path-method') ?
                    $this->_getAttribute($xml->{'uploadable'}, 'path-method') : null;
                $config['callback'] = $this->_isAttributeSet($xmlUploadable, 'callback') ?
                    $this->_getAttribute($xml->{'uploadable'}, 'callback') : null;
                $config['fileMimeTypeField'] = null;
                $config['fileNameField'] = null;
                $config['filePathField'] = null;
                $config['fileSizeField'] = null;
                $config['filenameGenerator'] = $this->_isAttributeSet($xmlUploadable, 'filename-generator') ?
                    $this->_getAttribute($xml->{'uploadable'}, 'filename-generator') :
                    Validator::FILENAME_GENERATOR_NONE;
                $config['maxSize'] = $this->_isAttributeSet($xmlUploadable, 'max-size') ?
                    (float) $this->_getAttribute($xml->{'uploadable'}, 'max-size') :
                    0.0;
                $config['allowedTypes'] = $this->_isAttributeSet($xmlUploadable, 'allowed-types') ?
                    $this->_getAttribute($xml->{'uploadable'}, 'allowed-types') :
                    '';
                $config['disallowedTypes'] = $this->_isAttributeSet($xmlUploadable, 'disallowed-types') ?
                    $this->_getAttribute($xml->{'uploadable'}, 'disallowed-types') :
                    '';

                if (isset($xmlDoctrine->field)) {
                    foreach ($xmlDoctrine->field as $mapping) {
                        $mappingDoctrine = $mapping;
                        $mapping = $mapping->children(self::GEDMO_NAMESPACE_URI);

                        $field = $this->_getAttribute($mappingDoctrine, 'name');

                        if (isset($mapping->{'uploadable-file-mime-type'})) {
                            $config['fileMimeTypeField'] = $field;
                        } elseif (isset($mapping->{'uploadable-file-size'})) {
                            $config['fileSizeField'] = $field;
                        } elseif (isset($mapping->{'uploadable-file-name'})) {
                            $config['fileNameField'] = $field;
                        } elseif (isset($mapping->{'uploadable-file-path'})) {
                            $config['filePathField'] = $field;
                        }
                    }
                }

                Validator::validateConfiguration($meta, $config);
            }
        }
    }
}
