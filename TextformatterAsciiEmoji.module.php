<?php namespace ProcessWire;

class TextformatterAsciiEmoji extends WireData implements Module, ConfigurableModule
{

    public static function getModuleInfo()
    {
        return array(
            'title' => 'ASCII Emoji Textformatter',
            'version' => '0.1.2',
            'summary' => 'Replaces ASCII emojis with their Unicode colored emoji equivalents.',
            'author' => 'wbmnfktr feat. ChatGPT',
            'icon' => 'smile-o',
            'autoload' => false,
            'singular' => true,
            'requires' => array('ProcessWire>=3.0.0'),
        );
    }

    public function __construct()
    {
        parent::__construct();
        $this->set('emojiMap', '');
    }

    public function init()
    {}

    public function getEmojiMap()
    {
        $map = $this->emojiMap;
        $map = explode("\n", $map);
        $emojiMap = [];

        foreach ($map as $entry) {
            $parts = explode("=>", $entry);
            if (count($parts) === 2) {
                // Validate and sanitize the ASCII and Unicode parts
                $ascii = $this->sanitizer->text(trim($parts[0]));
                $unicode = $this->sanitizer->text(trim($parts[1]));

                // Ensure the mapping is not empty after sanitization
                if ($ascii !== '' && $unicode !== '') {
                    $emojiMap[$ascii] = $unicode;
                }
            }
        }

        return $emojiMap;
    }

    public function formatValue(Page $page, Field $field, &$value)
    {
        $emojiMap = $this->getEmojiMap();

        foreach ($emojiMap as $ascii => $unicode) {
            // Escape any special regex characters in the ASCII string
            $ascii = preg_quote($ascii, '/');

            // Match the ASCII emoji only if it's surrounded by whitespace, punctuation, or at the start/end of the string
            $pattern = '/(?<=^|\s|[\p{P}\p{S}])' . $ascii . '(?=$|\s|[\p{P}\p{S}])/u';

            $value = preg_replace($pattern, $unicode, $value);
        }
    }

    public function getModuleConfigInputfields(array $data)
    {
        $inputfields = new InputfieldWrapper();

        $f = $this->wire('modules')->get('InputfieldTextarea');
        $f->attr('name', 'emojiMap');
        $f->label = $this->_('Emoji Map');
        $f->description = $this->_('Enter one mapping per line, with the format "ASCII => Unicode".');
        $f->notes = $this->_('Example: ":) => 😊"');
        $f->value = isset($data['emojiMap']) ? $data['emojiMap'] : $this->emojiMap;
        $inputfields->add($f);

        return $inputfields;
    }

    public function setConfigData(array $data)
    {
        $this->set('emojiMap', isset($data['emojiMap']) ? $data['emojiMap'] : '');
    }
}
