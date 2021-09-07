<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parser_item extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'ID';

    protected $fillable = [
        'Status',
        'Last_modified',
        'New',
        'Name',
        'Articul',
        'Url',
        'Is_available',
        'Price',
        'Price_action',
        'Quantity',
        'Series',
        'Components',
        'Accessories',
        'Params',
        'site_id',
        'proxy_ip',
    ];


    public static function updateOrCreate(array $attributes, array $values = array())
    {
        $instance = static::firstOrNew($attributes);

        $instance->fill($values)->save();

        return $instance;
    }


    public static function firstOrNew(array $attributes)
    {
        if ( ! is_null($instance = static::where($attributes)->first()))
        {
            return $instance;
        }

        return new static($attributes);
    }

    public function edit($fields){
        $this->Last_modified = $this->freshTimestamp();
        $this->fill($fields);
        $this->save();
    }
    public function remove(){
        $this->delete();
    }
}
