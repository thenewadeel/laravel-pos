<div class="flex justify-center">
    <div class="ml-4">
        <table class="mt-4">
            <thead>
                <tr>
                    <th class="px-4 py-2">User</th>
                    <th class="px-4 py-2">Job name</th>
                    <th class="px-4 py-2">Job ID</th>
                    <th class="px-4 py-2">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach (App\Models\JobLog::ofUser(auth()->user()->id)->get() as $jobLog)
                    <tr>
                        <td class="px-4 py-2">{{ $jobLog->user->name() ?? 'unkn' }}</td>
                        <td class="px-4 py-2">{{ $jobLog->job_name }}</td>
                        <td class="px-4 py-2">{{ $jobLog->job_id }}</td>
                        <td class="px-4 py-2">{{ $jobLog->status }}</td>
                        <td class="px-4 py-2">
                            @if ($jobLog->progress)
                                <div class="progress" style="height: 1.5rem;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ $jobLog->progress }}%;" aria-valuenow="{{ $jobLog->progress }}"
                                        aria-valuemin="0" aria-valuemax="100">{{ $jobLog->progress }}%</div>
                                </div>
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
