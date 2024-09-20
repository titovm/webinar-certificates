<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Base64Image extends Component
{
    public $src;
    public $alt;
    public $class;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($src, $alt = '', $class = '')
    {
        $this->src = $this->getBase64Image($src);
        $this->alt = $alt;
        $this->class = $class;
    }

    /**
     * Get the base64-encoded image.
     *
     * @param string $src
     * @return string
     */
    protected function getBase64Image($src)
    {
        $path = public_path($src);

        if (file_exists($path)) {
            $fileContents = file_get_contents($path);
            $mimeType = mime_content_type($path);
            return 'data:' . $mimeType . ';base64,' . base64_encode($fileContents);
        }

        return '';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.base64-image');
    }
}
