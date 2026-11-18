<div class="md:col-span-4" x-data="{ mode: @js(old('attendance_mode', $employee->attendance_mode ?? 'fixed')) }">
    <label class="block font-semibold text-sm mb-1.5" for="employee-attendance-mode">ប្រភេទម៉ោងការងារ</label>
    <select id="employee-attendance-mode" name="attendance_mode" x-model="mode" required class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
        <option value="fixed" @selected(old('attendance_mode', $employee->attendance_mode ?? 'fixed') === 'fixed')>ម៉ោងថេរ</option>
        <option value="flexible" @selected(old('attendance_mode', $employee->attendance_mode ?? 'fixed') === 'flexible')>ម៉ោងបត់បែន</option>
    </select>
    <div x-show="mode === 'fixed'" class="mt-3">
        <label class="block font-semibold text-sm mb-1.5" for="employee-shift">វេនការងារ</label>
        <select id="employee-shift" name="shift_id" :required="mode === 'fixed'" :disabled="mode === 'flexible'" class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
            <option value="">ជ្រើសវេនការងារ</option>
            @foreach($shifts as $shift)
                <option value="{{ $shift->id }}" @selected(old('shift_id', $employee->shift_id ?? '') == $shift->id)>{{ $shift->name }}</option>
            @endforeach
        </select>
    </div>
    <p x-show="mode === 'flexible'" x-cloak style="font-size:12px;line-height:1.9;color:#64748b;margin-top:10px;">មិនកំណត់វេន និងមិនគណនាមកយឺត។ នៅតែស្កេន QR និងពិនិត្យ GPS។</p>
</div>
