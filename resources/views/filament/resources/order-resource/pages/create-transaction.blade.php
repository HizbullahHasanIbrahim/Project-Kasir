<x-filament-panels::page>
    <x-filament::grid class="gap-6 items-start" default="2">
        <x-filament::section>
            <x-slot name="heading">
                Scan Barcode
            </x-slot>
            <x-slot name="description">
                Scan the product barcode to add it to the order.
            </x-slot>
            <input
                type="text"
                class="w-full text-sm h-10 dark:bg-zinc-800 dark:text-white rounded-md border shadow-sm border-zinc-200 dark:border-zinc-700"
                wire:model.defer="barcode"
                wire:keydown.enter="addProductByBarcode"
                placeholder="Scan barcode and press Enter"
                autofocus
            />
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Select Products
            </x-slot>
            <x-slot name="description">
                Choose the products you want to order.
            </x-slot>
            {{ $this->form }}
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Order Details
            </x-slot>
            <div class="-mx-4 flow-root sm:mx-0">
                <form wire:submit="finalizeOrder">
                    <x-table>
                        <colgroup>
                            <col class="w-full sm:w-1/2">
                            <col class="sm:w-1/6">
                            <col class="sm:w-1/6">
                            <col class="sm:w-1/6">
                        </colgroup>
                        <x-table.thead>
                            <tr>
                                <x-table.th>Name</x-table.th>
                                <x-table.th>Quantity</x-table.th>
                                <x-table.th>Price</x-table.th>
                                <x-table.th>Action</x-table.th>
                            </tr>
                        </x-table.thead>
                        <tbody>
                        @forelse ($record->orderDetails as $orderDetail)
                            <x-table.tr>
                                <x-table.td>
                                    <div class="font-medium dark:text-white text-zinc-900">
                                        {{ $orderDetail->product->name }}
                                    </div>
                                    <div class="mt-1 truncate text-zinc-500 dark:text-zinc-400">
                                        Current stock: {{ $orderDetail->product->stock_quantity }}
                                    </div>
                                </x-table.td>
                                <x-table.td>
                                    <input
                                        class="w-20 text-sm h-8 dark:bg-zinc-800 dark:text-white rounded-md border shadow-sm border-zinc-200 dark:border-zinc-700"
                                        type="number"
                                        value="{{ $orderDetail->quantity }}"
                                        wire:change="updateQuantity({{ $orderDetail->id }}, $event.target.value)"
                                        min="1"
                                        max="{{ $orderDetail->product->stock_quantity }}"
                                    />
                                </x-table.td>
                                <x-table.td class="text-right">
                                    {{ number_format($orderDetail->price * $orderDetail->quantity) }}
                                </x-table.td>
                                <x-table.td>
                                    <button type="button" wire:click="removeProduct({{ $orderDetail->id }})">
                                        @svg('heroicon-o-x-mark', [ 'width' => '20px' ])
                                    </button>
                                </x-table.td>
                            </x-table.tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-5 text-center dark:text-zinc-500 text-zinc-500">
                                    No products selected.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="2" class="hidden text-right text-sm font-normal text-zinc-500 sm:table-cell">
                                Subtotal
                            </th>
                            <td class="text-right text-sm text-zinc-500">
                                {{ number_format($record->orderDetails->sum('subtotal')) }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2" class="hidden text-right text-sm font-normal dark:text-zinc-400 sm:table-cell">
                                Discount (%)
                            </th>
                            <td class="text-right text-sm text-zinc-500">
                                <input
                                    class="w-20 text-sm h-8 dark:bg-zinc-800 dark:text-white rounded-md border shadow-sm border-zinc-200 dark:border-zinc-700"
                                    type="number"
                                    wire:model.lazy="discount"
                                    min="0"
                                    max="100"
                                    placeholder="Discount (%)"
                                />
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2" class="hidden text-right text-sm font-semibold dark:text-white sm:table-cell">
                                Total
                            </th>
                            <td class="text-right text-sm font-semibold dark:text-white">
                                @php
                                    $subtotal = $record->orderDetails->sum('subtotal');
                                    $discountValue = ($discount / 100) * $subtotal;
                                    $total = $subtotal - $discountValue;
                                @endphp
                                {{ number_format($total) }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2" class="hidden text-right text-sm font-normal dark:text-zinc-400 sm:table-cell">
                                Customer Cash
                            </th>
                            <td class="text-right text-sm text-zinc-500">
                                <input
                                    class="w-30 text-sm h-8 dark:bg-zinc-800 dark:text-white rounded-md border shadow-sm border-zinc-200 dark:border-zinc-700"
                                    type="number"
                                    wire:model.lazy="customer_cash"
                                    min="0"
                                    placeholder="Enter cash amount"
                                />
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2" class="hidden text-right text-sm font-semibold dark:text-white sm:table-cell">
                                Change
                            </th>
                            <td class="text-right text-sm font-semibold dark:text-white">
                                {{ number_format(max(0, $customer_cash - $total)) }}
                            </td>
                        </tr>

                        </tfoot>
                    </x-table>

                    <div class="flex justify-end mt-10">
                        <x-filament::button type="button" color="gray" wire:click="saveAsDraft">
                            Save as Draft
                        </x-filament::button>
                        <x-filament::button type="submit" class="ml-2">
                            Create Transaction
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </x-filament::section>
    </x-filament::grid>
</x-filament-panels::page>
