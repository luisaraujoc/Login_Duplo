<?php

namespace App\Enums;

/**
 * Shared by categories and movements — mirrors the legacy "operacao"
 * (credito/debito) and "tipo" (1/0) values used throughout the app.
 */
enum CashFlowType: string
{
    case Credit = 'credit';
    case Debit = 'debit';
}
