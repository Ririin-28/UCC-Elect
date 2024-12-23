<!-- President Candidates List Section -->
<div class="container mt-4">
    <h3 class="text-center">President Candidates List</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Candidate ID</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Gender</th>
                    <th>Course</th>
                    <th>Year & Section</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="candidatesList">
                <!-- Candidate Rows will be dynamically added here -->
                <tr>
                    <td colspan="9" class="text-center">No candidates added yet.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    let vicePresidentCandidates = [];

// Function to add Vice President Candidate
function addVicePresidentCandidate() {
    const candidateID = document.getElementById('vicePresidentCandidateID').value;
    const lastName = document.getElementById('vicePresidentLastName').value;
    const firstName = document.getElementById('vicePresidentFirstName').value;
    const middleName = document.getElementById('vicePresidentMiddleName').value || 'N/A';
    const gender = document.getElementById('vicePresidentGender').value;
    const course = document.getElementById('vicePresidentCourse').value;
    const yearSection = document.getElementById('vicePresidentYearSection').value;

    const candidate = { candidateID, lastName, firstName, middleName, gender, course, yearSection };
    vicePresidentCandidates.push(candidate);

    document.getElementById('vicePresidentCandidateForm').reset();

    const modal = bootstrap.Modal.getInstance(document.getElementById('addVicePresidentModal'));
    modal.hide();

    updateVicePresidentCandidatesList();
}

// Function to update Vice President Candidates List
function updateVicePresidentCandidatesList() {
    const tableBody = document.getElementById('vicePresidentCandidatesList');
    tableBody.innerHTML = '';

    vicePresidentCandidates.forEach((candidate, index) => {
        tableBody.innerHTML += `
            <tr>
                <td>${index + 1}</td>
                <td>${candidate.candidateID}</td>
                <td>${candidate.lastName}</td>
                <td>${candidate.firstName}</td>
                <td>${candidate.middleName}</td>
                <td>${candidate.gender}</td>
                <td>${candidate.course}</td>
                <td>${candidate.yearSection}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editVicePresidentCandidate(${index})">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteVicePresidentCandidate(${index})">Delete</button>
                </td>
            </tr>
        `;
    });

    if (vicePresidentCandidates.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="9" class="text-center">No candidates added yet.</td></tr>`;
    }
}

// Function to delete Vice President Candidate
function deleteVicePresidentCandidate(index) {
    vicePresidentCandidates.splice(index, 1);
    updateVicePresidentCandidatesList();
}

// Function to edit Vice President Candidate
function editVicePresidentCandidate(index) {
    const candidate = vicePresidentCandidates[index];
    document.getElementById('vicePresidentCandidateID').value = candidate.candidateID;
    document.getElementById('vicePresidentLastName').value = candidate.lastName;
    document.getElementById('vicePresidentFirstName').value = candidate.firstName;
    document.getElementById('vicePresidentMiddleName').value = candidate.middleName !== 'N/A' ? candidate.middleName : '';
    document.getElementById('vicePresidentGender').value = candidate.gender;
    document.getElementById('vicePresidentCourse').value = candidate.course;
    document.getElementById('vicePresidentYearSection').value = candidate.yearSection;

    const modal = new bootstrap.Modal(document.getElementById('addVicePresidentModal'));
    modal.show();

    deleteVicePresidentCandidate(index);
}

</script>




<!-- Secretary Candidates List Section -->
<div class="container mt-4">
    <h3 class="text-center">Secretary Candidates List</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Candidate ID</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Gender</th>
                    <th>Course</th>
                    <th>Year & Section</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="secretaryCandidatesList">
                <!-- Candidate Rows will be dynamically added here -->
                <tr>
                    <td colspan="9" class="text-center">No candidates added yet.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    let secretaryCandidates = [];

// Function to add Secretary Candidate
function addSecretaryCandidate() {
    const candidateID = document.getElementById('secretaryCandidateID').value;
    const lastName = document.getElementById('secretaryLastName').value;
    const firstName = document.getElementById('secretaryFirstName').value;
    const middleName = document.getElementById('secretaryMiddleName').value || 'N/A';
    const gender = document.getElementById('secretaryGender').value;
    const course = document.getElementById('secretaryCourse').value;
    const yearSection = document.getElementById('secretaryYearSection').value;

    const candidate = { candidateID, lastName, firstName, middleName, gender, course, yearSection };
    secretaryCandidates.push(candidate);

    document.getElementById('secretaryCandidateForm').reset();

    const modal = bootstrap.Modal.getInstance(document.getElementById('addSecretaryModal'));
    modal.hide();

    updateSecretaryCandidatesList();
}

// Function to update Secretary Candidates List
function updateSecretaryCandidatesList() {
    const tableBody = document.getElementById('secretaryCandidatesList');
    tableBody.innerHTML = '';

    secretaryCandidates.forEach((candidate, index) => {
        tableBody.innerHTML += `
            <tr>
                <td>${index + 1}</td>
                <td>${candidate.candidateID}</td>
                <td>${candidate.lastName}</td>
                <td>${candidate.firstName}</td>
                <td>${candidate.middleName}</td>
                <td>${candidate.gender}</td>
                <td>${candidate.course}</td>
                <td>${candidate.yearSection}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editSecretaryCandidate(${index})">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteSecretaryCandidate(${index})">Delete</button>
                </td>
            </tr>
        `;
    });

    if (secretaryCandidates.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="9" class="text-center">No candidates added yet.</td></tr>`;
    }
}

// Function to delete Secretary Candidate
function deleteSecretaryCandidate(index) {
    secretaryCandidates.splice(index, 1);
    updateSecretaryCandidatesList();
}

// Function to edit Secretary Candidate
function editSecretaryCandidate(index) {
    const candidate = secretaryCandidates[index];
    document.getElementById('secretaryCandidateID').value = candidate.candidateID;
    document.getElementById('secretaryLastName').value = candidate.lastName;
    document.getElementById('secretaryFirstName').value = candidate.firstName;
    document.getElementById('secretaryMiddleName').value = candidate.middleName !== 'N/A' ? candidate.middleName : '';
    document.getElementById('secretaryGender').value = candidate.gender;
    document.getElementById('secretaryCourse').value = candidate.course;
    document.getElementById('secretaryYearSection').value = candidate.yearSection;

    const modal = new bootstrap.Modal(document.getElementById('addSecretaryModal'));
    modal.show();

    deleteSecretaryCandidate(index);
}

</script>