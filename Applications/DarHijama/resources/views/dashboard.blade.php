<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dar Hijama Dashboard</title>
</head>
<body>
    <main>
        <h1>Dar Hijama</h1>
        <section aria-label="Practice overview">
            <p>Patients: {{ $patientCount }}</p>
            <p>Active practitioners: {{ $practitionerCount }}</p>
            <p>Upcoming appointments: {{ $upcomingAppointmentCount }}</p>
        </section>
    </main>
</body>
</html>
