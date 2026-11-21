<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class TaxSetting extends BaseModel
{
    protected static string $table = 'tax_settings';
    protected static string $pk    = 'id';

    public static function current(): ?array
    {
        return Database::first("SELECT * FROM tax_settings WHERE id = 1");
    }
}
