<div class="p-0" style="width: 100%; max-height: 75vh; overflow: hidden;">
    <div class="table-responsive p-3" style="max-height: 65vh; overflow-y: auto;">
        <table class="table table-bordered table-striped table-hover align-middle table-sm mb-0">
            <thead class="table-success text-center">
                <tr>
                    <th style="min-width: 60px;">ID</th>
                    <th style="min-width: 150px;">School Year</th>
                    <th style="min-width: 150px;">LRN</th>
                    <th style="min-width: 150px;">Grade Level</th>
                    <th style="min-width: 150px;">PSA No.</th>
                    <th style="min-width: 150px;">Last Name</th>
                    <th style="min-width: 150px;">First Name</th>
                    <th style="min-width: 130px;">Middle Name</th>
                    <th style="min-width: 150px;">Birthdate</th>
                    <th style="min-width: 70px;">Age</th>
                    <th style="min-width: 80px;">Sex</th>
                    <th style="min-width: 180px;">Birthplace</th>
                    <th style="min-width: 150px;">Religion</th>
                    <th style="min-width: 220px;">Mother Tongue</th>
                    <th style="min-width: 450px;">Current Address</th>
                    <th style="min-width: 450px;">Permanent Address</th>
                    <th style="min-width: 200px;">Father</th>
                    <th style="min-width: 200px;">Mother’s Maiden</th>
                    <th style="min-width: 200px;">Guardian</th>
                    <th style="min-width: 150px;">Last Grade</th>
                    <th style="min-width: 170px;">Last School Attended</th>
                    <th style="min-width: 150px;">School ID</th>
                    <th style="min-width: 150px;">Blended</th>
                    <th style="min-width: 150px;">Homeschool</th>
                    <th style="min-width: 150px;">Modular Print</th>
                    <th style="min-width: 155px;">Modular Digital</th>
                    <th style="min-width: 130px;">Online</th>
                    <th style="min-width: 220px;">Date Submitted</th>
                </tr>
            </thead>
            <tbody id="newStudentsBody">
                <?php
                $query = "SELECT * FROM basic_enrollment_students ORDER BY date_submitted DESC";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['student_id']}</td>
                            <td>{$row['school_year']}</td>
                            <td>{$row['lrn']}</td>
                            <td>{$row['grade_level']}</td>
                            <td>{$row['psa_birth_cert_no']}</td>
                            <td>{$row['last_name']}</td>
                            <td>{$row['first_name']}</td>
                            <td>{$row['middle_name']}</td>
                            <td>{$row['birthdate']}</td>
                            <td>{$row['age']}</td>
                            <td>{$row['sex']}</td>
                            <td>{$row['place_of_birth']}</td>
                            <td>{$row['religion']}</td>
                            <td>{$row['mother_tongue']}</td>
                            <td>{$row['current_address']}</td>
                            <td>{$row['permanent_address']}</td>
                            <td>{$row['father_name']}</td>
                            <td>{$row['mother_maiden_name']}</td>
                            <td>{$row['guardian_name']}</td>
                            <td>{$row['last_grade_completed']}</td>
                            <td>{$row['last_school_attended']}</td>
                            <td>{$row['last_school_id']}</td>
                            <td>" . ($row['blended'] ? '✔️' : '') . "</td>
                            <td>" . ($row['homeschooling'] ? '✔️' : '') . "</td>
                            <td>" . ($row['modular_print'] ? '✔️' : '') . "</td>
                            <td>" . ($row['modular_digital'] ? '✔️' : '') . "</td>
                            <td>" . ($row['online'] ? '✔️' : '') . "</td>
                            <td>{$row['date_submitted']}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='27' class='text-center'>No Students Found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
