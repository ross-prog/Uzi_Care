<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Statistical Report - {{ $monthName }} {{ $year }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #2d3748;
            background: white;
            margin: 15mm;
        }
        
        .page {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 15px;
        }
        
        .clinic-name {
            font-size: 22px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        
        .clinic-subtitle {
            color: #6b7280;
            margin: 2px 0 0 0;
        }
        
        .report-title {
            font-size: 20px;
            color: #1f2937;
            margin: 10px 0 5px 0;
            font-weight: 600;
        }
        
        .report-period {
            font-size: 14px;
            color: #4b5563;
            margin: 0;
        }
        
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 0;
        }
        
        .summary-section {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .summary-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e40af;
            margin: 0 0 15px 0;
            text-align: center;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        
        .summary-card {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .summary-number {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            display: block;
            margin-bottom: 4px;
        }
        
        .summary-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 12px 0;
            padding-bottom: 6px;
            border-bottom: 2px solid #2563eb;
        }
        
        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .chart-box {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 15px;
            background: white;
        }
        
        .chart-title {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 10px 0;
        }
        
        .chart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .chart-item:last-child {
            border-bottom: none;
        }
        
        .chart-label {
            font-size: 10px;
            color: #4b5563;
            flex: 1;
        }
        
        .chart-bar {
            height: 6px;
            background: #3b82f6;
            border-radius: 3px;
            margin: 0 8px;
            min-width: 20px;
        }
        
        .chart-value {
            font-size: 10px;
            font-weight: 600;
            color: #1f2937;
            min-width: 20px;
            text-align: right;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .table th {
            background: #1e40af;
            color: white;
            font-weight: 600;
            font-size: 10px;
            padding: 8px 6px;
            text-align: left;
            border-bottom: 1px solid #d1d5db;
        }
        
        .table td {
            padding: 6px;
            font-size: 10px;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .rank-badge {
            background: #2563eb;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: bold;
            margin-right: 8px;
        }
        
        .no-data {
            text-align: center;
            color: #9ca3af;
            font-style: italic;
            padding: 30px;
            background: #f9fafb;
            border-radius: 6px;
            border: 1px dashed #d1d5db;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <h1 class="clinic-name">UZI CARE CLINIC</h1>
            <p class="clinic-subtitle">Comprehensive Healthcare Management System</p>
            <h2 class="report-title">Statistical Report</h2>
            <p class="report-period">{{ $monthName }}</p>
        </div>

        <!-- Meta Information -->
        <div class="meta-info">
            <div><strong>Generated:</strong> {{ $generatedAt }}</div>
            <div><strong>Report Type:</strong> Monthly Statistics</div>
            <div><strong>Status:</strong> Official Document</div>
        </div>

        <!-- Summary Statistics -->
        <div class="summary-section">
            <h3 class="summary-title">Executive Summary</h3>
            <div class="summary-grid">
                <div class="summary-card">
                    <span class="summary-number">{{ $data['patients']['total'] ?? 0 }}</span>
                    <div class="summary-label">Total Patients</div>
                </div>
                <div class="summary-card">
                    <span class="summary-number">{{ $data['patients']['thisMonth'] ?? 0 }}</span>
                    <div class="summary-label">Monthly Visits</div>
                </div>
                <div class="summary-card">
                    <span class="summary-number">{{ $data['patients']['today'] ?? 0 }}</span>
                    <div class="summary-label">Today's Visits</div>
                </div>
                <div class="summary-card">
                    <span class="summary-number">{{ $data['medicines'] ? $data['medicines']->count() : 0 }}</span>
                    <div class="summary-label">Medicines Used</div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="two-column">
            <!-- Visit Types Distribution -->
            <div class="chart-box">
                <h4 class="chart-title">Visit Types Distribution</h4>
                @if($data['visitTypes'] && $data['visitTypes']->count() > 0)
                    @php
                        $maxCount = $data['visitTypes']->max('count');
                    @endphp
                    @foreach($data['visitTypes']->take(8) as $type)
                        <div class="chart-item">
                            <span class="chart-label">{{ $type->type ?? 'Unknown' }}</span>
                            <div class="chart-bar" style="width: {{ $maxCount > 0 ? ($type->count / $maxCount) * 100 : 0 }}%"></div>
                            <strong class="chart-value">{{ $type->count ?? 0 }}</strong>
                        </div>
                    @endforeach
                @else
                    <div class="no-data">No visit type data available</div>
                @endif
            </div>

            <!-- Department Distribution -->
            <div class="chart-box">
                <h4 class="chart-title">Department Distribution</h4>
                @if($data['departments'] && $data['departments']->count() > 0)
                    @php
                        $maxCount = $data['departments']->max('count');
                    @endphp
                    @foreach($data['departments']->take(8) as $dept)
                        <div class="chart-item">
                            <span class="chart-label">{{ $dept->department ?? 'Unknown' }}</span>
                            <div class="chart-bar" style="width: {{ $maxCount > 0 ? ($dept->count / $maxCount) * 100 : 0 }}%"></div>
                            <strong class="chart-value">{{ $dept->count ?? 0 }}</strong>
                        </div>
                    @endforeach
                @else
                    <div class="no-data">No department data available</div>
                @endif
            </div>
        </div>

        <!-- Top Medical Conditions -->
        <div class="section">
            <h3 class="section-title">Top Medical Conditions</h3>
            @if($data['topConditions'] && $data['topConditions']->count() > 0)
                @php
                    $totalConditions = $data['topConditions']->sum('count');
                @endphp
                <table class="table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Condition</th>
                            <th>Cases</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['topConditions']->take(10) as $index => $condition)
                            <tr>
                                <td><span class="rank-badge">{{ $index + 1 }}</span></td>
                                <td>{{ $condition->condition ?? 'Unknown' }}</td>
                                <td>{{ $condition->count ?? 0 }}</td>
                                <td>{{ $totalConditions > 0 ? number_format(($condition->count / $totalConditions) * 100, 1) : 0 }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">No condition data available</div>
            @endif
        </div>

        <!-- Medicine and Nurse Data -->
        <div class="two-column">
            <!-- Most Prescribed Medicines -->
            <div class="chart-box">
                <h4 class="chart-title">Most Prescribed Medicines</h4>
                @if($data['medicines'] && $data['medicines']->count() > 0)
                    @foreach($data['medicines']->take(10) as $medicine)
                        <div class="chart-item">
                            <span class="chart-label">{{ $medicine->name ?? 'Unknown' }}</span>
                            <strong class="chart-value">{{ $medicine->count ?? 0 }}x</strong>
                        </div>
                    @endforeach
                @else
                    <div class="no-data">No medicine data available</div>
                @endif
            </div>

            <!-- Nurse Workload -->
            <div class="chart-box">
                <h4 class="chart-title">Nurse Workload Distribution</h4>
                @if($data['nurses'] && $data['nurses']->count() > 0)
                    @foreach($data['nurses'] as $nurse)
                        <div class="chart-item">
                            <span class="chart-label">{{ $nurse->name ?? 'Unknown' }}</span>
                            <strong class="chart-value">{{ $nurse->patient_count ?? 0 }} patients</strong>
                        </div>
                    @endforeach
                @else
                    <div class="no-data">No nurse workload data available</div>
                @endif
            </div>
        </div>

        <!-- Monthly Performance Summary -->
        <div class="section">
            <h3 class="section-title">Monthly Performance Summary</h3>
            <table class="table">
                <tr>
                    <td><strong>Total Patient Visits</strong></td>
                    <td>{{ $data['patients']['thisMonth'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td><strong>Unique Patients</strong></td>
                    <td>{{ $data['patients']['total'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td><strong>Departments Served</strong></td>
                    <td>{{ $data['departments'] ? $data['departments']->count() : 0 }}</td>
                </tr>
                <tr>
                    <td><strong>Visit Types</strong></td>
                    <td>{{ $data['visitTypes'] ? $data['visitTypes']->count() : 0 }}</td>
                </tr>
                <tr>
                    <td><strong>Active Nurses</strong></td>
                    <td>{{ $data['nurses'] ? $data['nurses']->count() : 0 }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                <strong>UZI CARE CLINIC</strong> | Comprehensive Healthcare Management System<br>
                This report was automatically generated on {{ $generatedAt }}<br>
                <em>Confidential Document - For Internal Use Only</em>
            </p>
        </div>
    </div>
</body>
</html>