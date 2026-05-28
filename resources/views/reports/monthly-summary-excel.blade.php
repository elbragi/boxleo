<html>
<head>
<style>
  body { font-family: Calibri, Arial, sans-serif; font-size: 10pt; }
  table { border-collapse: collapse; width: 100%; }
  td, th { border: 1px solid #ccc; padding: 4px 7px; white-space: nowrap; }
  .title-row td { background-color: #2e7d32; color: #fff; font-size: 13pt; font-weight: bold; text-align: center; border: none; }
  .sub-row td { background-color: #388e3c; color: #fff; font-size: 11pt; font-weight: bold; text-align: center; border: none; }
  .legend-row td { background-color: #f5f5f5; font-size: 8pt; color: #555; border: none; }
  .header-row th { background-color: #1a3c4e; color: #fff; font-weight: bold; text-align: center; font-size: 9pt; }
  .data-row td { text-align: center; }
  .data-row td.name-col { text-align: left; font-weight: bold; }
  .data-row td.num-col { text-align: center; }
  .even { background-color: #ffffff; }
  .odd  { background-color: #f8fafc; }
  .present  { color: #2e7d32; font-weight: bold; }
  .late     { color: #c62828; font-weight: bold; }
  .absent   { color: #e65100; font-weight: bold; }
  .on-leave { color: #1565c0; font-weight: bold; }
  .in-field { color: #e65100; font-weight: bold; }
  .balance  { color: #0277bd; font-weight: bold; }
  .zero     { color: #bbb; }
</style>
</head>
<body>
<table>
  <tbody>

    {{-- Title --}}
    <tr class="title-row">
      <td colspan="{{ 6 + count($leaveTypes) }}">BOXLEO COURIER AND FULFILMENT SERVICES</td>
    </tr>
    <tr class="sub-row">
      <td colspan="{{ 6 + count($leaveTypes) }}">{{ strtoupper($monthLabel) }} ATTENDANCE REPORT</td>
    </tr>

    {{-- Legend --}}
    <tr class="legend-row">
      <td colspan="{{ 6 + count($leaveTypes) }}">
        Present: ✓ &nbsp;|&nbsp;
        Late: Φ &nbsp;|&nbsp;
        Absent: × &nbsp;|&nbsp;
        In Field: £ &nbsp;|&nbsp;
        Annual Leave: ⊙ &nbsp;|&nbsp;
        Sick: ⊕ &nbsp;|&nbsp;
        Maternity: M &nbsp;|&nbsp;
        Paternity: P &nbsp;|&nbsp;
        Compassionate: φ &nbsp;|&nbsp;
        Study: ∞ &nbsp;|&nbsp;
        Sat Off: + &nbsp;|&nbsp;
        Sun Comp: $
      </td>
    </tr>

    {{-- Blank row --}}
    <tr><td colspan="{{ 6 + count($leaveTypes) }}" style="border:none;height:6px"></td></tr>

    {{-- Column headers --}}
    <tr class="header-row">
      <th>#</th>
      <th>Employee Name</th>
      <th>Present ✓</th>
      <th>Late Φ</th>
      <th>Absent ×</th>
      @foreach ($leaveTypes as $lt)
        <th>{{ $lt['short'] }}</th>
      @endforeach
      <th>In Field £</th>
      <th>Leave Balance</th>
    </tr>

    {{-- Data rows --}}
    @foreach ($report as $i => $row)
    <tr class="data-row {{ $i % 2 === 0 ? 'even' : 'odd' }}">
      <td class="num-col">{{ $i + 1 }}</td>
      <td class="name-col">{{ $row['name'] }}</td>
      <td class="num-col {{ $row['present'] > 0 ? 'present' : 'zero' }}">{{ $row['present'] }}</td>
      <td class="num-col {{ $row['late'] > 0 ? 'late' : 'zero' }}">{{ $row['late'] }}</td>
      <td class="num-col {{ $row['absent'] > 0 ? 'absent' : 'zero' }}">{{ $row['absent'] }}</td>
      @foreach ($leaveTypes as $lt)
        @php $val = $row['lt_' . $lt['id']] ?? 0; @endphp
        <td class="num-col {{ $val > 0 ? 'on-leave' : 'zero' }}">{{ $val }}</td>
      @endforeach
      <td class="num-col {{ $row['in_field'] > 0 ? 'in-field' : 'zero' }}">{{ $row['in_field'] }}</td>
      <td class="num-col balance">{{ $row['leave_balance'] }}</td>
    </tr>
    @endforeach

    {{-- Totals row --}}
    @php
      $totPresent  = collect($report)->sum('present');
      $totLate     = collect($report)->sum('late');
      $totAbsent   = collect($report)->sum('absent');
      $totInField  = collect($report)->sum('in_field');
    @endphp
    <tr style="background-color:#e8f5e9; font-weight:bold;">
      <td colspan="2" style="text-align:right; font-weight:bold;">TOTALS</td>
      <td class="num-col present">{{ $totPresent }}</td>
      <td class="num-col late">{{ $totLate }}</td>
      <td class="num-col absent">{{ $totAbsent }}</td>
      @foreach ($leaveTypes as $lt)
        @php $tot = collect($report)->sum('lt_' . $lt['id']); @endphp
        <td class="num-col on-leave">{{ $tot ?: '' }}</td>
      @endforeach
      <td class="num-col in-field">{{ $totInField }}</td>
      <td></td>
    </tr>

  </tbody>
</table>
</body>
</html>
