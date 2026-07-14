<?php

namespace App\Services\Sdui\Renderer;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class TemplateRenderer
{
    /**
     * Render a template with given data.
     * 
     * @param string $templateName Name of the template file without extension.
     * @param array $data Data to inject into the template.
     * @return array|null The rendered SDUI structure or null on failure.
     */
    public function render(string $templateName, array $data = []): ?array
    {
        $path = resource_path("sdui/templates/{$templateName}.json");
        
        if (!File::exists($path)) {
            Log::error("SDUI Template not found: {$path}");
            return null;
        }

        $jsonContent = File::get($path);
        $template = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("SDUI Template JSON decode error in {$path}: " . json_last_error_msg());
            return null;
        }

        return $this->traverseAndInterpolate($template, $data);
    }

    /**
     * Recursively traverse the array and interpolate variables.
     */
    private function traverseAndInterpolate(mixed $node, array $data): mixed
    {
        if (is_array($node)) {
            $result = [];
            foreach ($node as $key => $value) {
                $result[$key] = $this->traverseAndInterpolate($value, $data);
            }
            return $result;
        }

        if (is_string($node)) {
            // Check for exact object/array replacement like "{{ items }}"
            if (preg_match('/^{{\s*([a-zA-Z0-9_]+)\s*}}$/', trim($node), $matches)) {
                $key = $matches[1];
                if (array_key_exists($key, $data) && !is_scalar($data[$key])) {
                    return $data[$key]; // Inject array or object directly
                }
            }

            // String interpolation for scalar variables
            return preg_replace_callback('/{{\s*([a-zA-Z0-9_]+)\s*}}/', function ($matches) use ($data) {
                $key = $matches[1];
                return array_key_exists($key, $data) ? $data[$key] : $matches[0];
            }, $node);
        }

        return $node;
    }
}
