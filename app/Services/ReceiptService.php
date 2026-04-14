<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReceiptService
{
    public function generateForOrder(Order $order, ?Payment $payment = null, ?User $actor = null): Receipt
    {
        $order->loadMissing([
            'buyer',
            'items.product',
            'items.supplier',
            'payments',
        ]);

        $disk = (string) config('filesystems.default', 'public');
        $timestamp = now();
        $path = sprintf(
            'receipts/%s/%s-%s.pdf',
            $timestamp->format('Y/m'),
            Str::slug($order->order_number),
            $timestamp->format('His'),
        );

        Storage::disk($disk)->put($path, $this->renderPdf($order, $payment));

        $receipt = Receipt::query()->create([
            'order_id' => $order->id,
            'payment_id' => $payment?->id,
            'file_path' => $path,
            'file_disk' => $disk,
            'generated_by_user_id' => $actor?->id,
            'generated_at' => $timestamp,
        ]);

        activity()
            ->performedOn($order)
            ->causedBy($actor)
            ->event('receipt.generated')
            ->withProperties([
                'receipt_id' => $receipt->id,
                'payment_id' => $payment?->id,
                'file_path' => $path,
            ])
            ->log('receipt.generated');

        return $receipt;
    }

    private function renderPdf(Order $order, ?Payment $payment = null): string
    {
        $lines = [
            config('app.name').' Receipt',
            'Receipt date: '.now()->toDateTimeString(),
            'Order number: '.$order->order_number,
            'Buyer: '.($order->buyer?->company_name ?? 'N/A'),
            'Status: '.str($order->status->value)->replace('_', ' ')->title(),
            'Order total (USD): '.number_format((float) $order->order_total, 2),
        ];

        if ($payment) {
            $lines[] = 'Payment method: '.str($payment->method->value)->replace('_', ' ')->title();
            $lines[] = 'Payment amount ('.$payment->currency.'): '.number_format((float) $payment->amount, 2);
            $lines[] = 'Payment status: '.str($payment->status->value)->replace('_', ' ')->title();
        }

        $lines[] = 'Items:';

        foreach ($order->items as $item) {
            $lines[] = sprintf(
                '- %s | Qty: %s | Unit USD: %s | Line USD: %s',
                $item->product_name_snapshot,
                number_format((float) $item->quantity, 2),
                number_format((float) $item->unit_price_usd, 2),
                number_format((float) $item->line_total_usd, 2),
            );
        }

        return $this->buildPdfDocument($lines);
    }

    /**
     * @param  array<int, string>  $lines
     */
    private function buildPdfDocument(array $lines): string
    {
        $content = "BT\n/F1 12 Tf\n50 760 Td\n";
        $firstLine = true;

        foreach ($lines as $line) {
            $escaped = $this->escapePdfText($line);

            if ($firstLine) {
                $content .= "({$escaped}) Tj\n";
                $firstLine = false;

                continue;
            }

            $content .= "0 -18 Td\n({$escaped}) Tj\n";
        }

        $content .= 'ET';

        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj",
            "4 0 obj\n<< /Length ".strlen($content)." >>\nstream\n{$content}\nendstream\nendobj",
            "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object."\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function escapePdfText(string $value): string
    {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\(', '\)'],
            preg_replace('/[^\x20-\x7E]/', ' ', $value) ?? $value,
        );
    }
}
