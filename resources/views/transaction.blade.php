<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite('resources/css/app.css')
    <title>Transaction</title>
</head>

<body>
    <div class="container mx-auto mt-8">
        <div class="overflow-x-auto max-h-[56vh] w-full mt-5">
            <table class="table table-zebra table-xs table-pin-rows table-pin-cols text-xs cursor-pointer">
                <thead>
                    <tr>
                        <td>No</td>
                        <td>LocationCode</td>
                        <td>InTime</td>
                        <td>OutTime</td>
                        <td>Status</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaction as $x => $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->LocationCode }}</td>
                            <td>{{ $item->InTime }}</td>
                            <td>{{ $item->OutTime }}</td>
                            <td>{{ $item->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 pagination">
            {{ $transaction->links('pagination::tailwind') }}
        </div>
    </div>
</body>

</html>
