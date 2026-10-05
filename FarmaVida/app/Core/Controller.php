<?php
abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require FARMAVIDA_ROOT . '/app/Views/' . $view . '.php';
    }
}
