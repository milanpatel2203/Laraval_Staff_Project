@props([
    'headers' => [],
    'pagination' => null,
    'empty' => false,
    'emptyMessage' => 'No records found.',
    'emptyColspan' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white border border-gray-200 rounded overflow-hidden']) }}>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
            @if(isset($header))
                <thead>
                    {{ $header }}
                </thead>
            @elseif(!empty($headers))
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50 text-gray-500 uppercase tracking-wider text-[11px]">
                        @foreach($headers as $h)
                            @if(is_array($h))
                                <th class="py-3 px-4 font-semibold {{ $h['class'] ?? '' }} {{ ($h['align'] ?? '') === 'right' ? 'text-right' : (($h['align'] ?? '') === 'center' ? 'text-center' : 'text-left') }}">
                                    {{ $h['label'] ?? '' }}
                                </th>
                            @else
                                <th class="py-3 px-4 font-semibold {{ str_contains(strtolower($h), 'action') ? 'text-right w-24' : '' }}">
                                    {{ $h }}
                                </th>
                            @endif
                        @endforeach
                    </tr>
                </thead>
            @endif

            <tbody class="divide-y divide-gray-100 text-[13px]">
                @if($empty)
                    <tr>
                        <td colspan="{{ $emptyColspan ?? (count($headers) ?: 10) }}" class="py-8 text-center text-gray-400">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @else
                    {{ $slot }}
                @endif
            </tbody>
        </table>
    </div>

    @if($pagination && method_exists($pagination, 'hasPages') && $pagination->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <span class="text-gray-500">
                Showing <span class="font-bold text-[#2D2D2D]">{{ $pagination->firstItem() }}</span> to <span class="font-bold text-[#2D2D2D]">{{ $pagination->lastItem() }}</span> of <span class="font-bold text-[#2D2D2D]">{{ $pagination->total() }}</span> records
            </span>
            <div class="custom-pagination">
                {{ $pagination->links() }}
            </div>
        </div>
    @endif
</div>
