<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CollectionExport implements FromCollection, WithHeadings
{
    protected $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        return collect($this->collection);
    }

    public function headings(): array
    {
        if ($this->collection->isEmpty()) return [];
        return array_keys((array) $this->collection->first());
    }
}
