// Static Dashboard JavaScript
class StaticAdminDashboard {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                this.toggleSidebar();
            });
        }

        // Modal save buttons
        const saveStudent = document.getElementById('saveStudent');
        const saveCourse = document.getElementById('saveCourse');
        const saveExamResult = document.getElementById('saveExamResult');
        const saveProgram = document.getElementById('saveProgram');
        const saveSpecialty = document.getElementById('saveSpecialty');
        const saveTranscript = document.getElementById('saveTranscript');

        if (saveStudent) saveStudent.addEventListener('click', () => this.saveStudent());
        if (saveCourse) saveCourse.addEventListener('click', () => this.saveCourse());
        if (saveExamResult) saveExamResult.addEventListener('click', () => this.saveExamResult());
        if (saveProgram) saveProgram.addEventListener('click', () => this.saveProgram());
        if (saveSpecialty) saveSpecialty.addEventListener('click', () => this.saveSpecialty());
        if (saveTranscript) saveTranscript.addEventListener('click', () => this.saveTranscript());

        // Delete confirmation
        const confirmDelete = document.getElementById('confirmDelete');
        if (confirmDelete) {
            confirmDelete.addEventListener('click', () => this.confirmDelete());
        }

        // Form validation
        this.setupFormValidation();

        // Auto-calculate GPA points for transcript
        this.setupTranscriptCalculation();
    }

    setupFormValidation() {
        const forms = ['studentForm', 'courseForm', 'examResultForm', 'programForm', 'specialtyForm', 'transcriptForm'];
        forms.forEach(formId => {
            const form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', (e) => e.preventDefault());
            }
        });
    }

    setupTranscriptCalculation() {
        const gradeSelect = document.getElementById('transcriptGrade');
        const creditHoursInput = document.getElementById('transcriptCreditHours');
        const gpaPointsInput = document.getElementById('transcriptGpaPoints');

        if (gradeSelect && creditHoursInput && gpaPointsInput) {
            const calculateGPA = () => {
                const grade = gradeSelect.value;
                const creditHours = parseFloat(creditHoursInput.value) || 0;
                
                const gradePoints = {
                    'A': 4.0,
                    'B': 3.0,
                    'C': 2.0,
                    'D': 1.0,
                    'F': 0.0
                };

                const points = gradePoints[grade] || 0;
                const totalPoints = points * creditHours;
                gpaPointsInput.value = totalPoints.toFixed(1);
            };

            gradeSelect.addEventListener('change', calculateGPA);
            creditHoursInput.addEventListener('input', calculateGPA);
        }
    }

    toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.toggle('show');
        }
    }

    // Student Management
    showStudentModal(studentId = null) {
        const modal = new bootstrap.Modal(document.getElementById('studentModal'));
        const form = document.getElementById('studentForm');
        if (form) form.reset();
        
        const studentIdInput = document.getElementById('studentId');
        const modalTitle = document.getElementById('studentModalTitle');
        
        if (studentIdInput) studentIdInput.value = '';
        if (modalTitle) modalTitle.textContent = 'Add Student';
        
        if (studentId) {
            if (modalTitle) modalTitle.textContent = 'Edit Student';
            if (studentIdInput) studentIdInput.value = studentId;
            // Populate form with dummy data for demonstration
            this.populateStudentForm(studentId);
        }
        modal.show();
    }

    populateStudentForm(studentId) {
        const fields = {
            'studentMatricule': 'MAT' + studentId.toString().padStart(3, '0'),
            'studentFullName': 'Student ' + studentId,
            'studentEmail': `student${studentId}@example.com`,
            'studentPhone': `+123456789${studentId}`
        };

        Object.entries(fields).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) element.value = value;
        });
    }

    saveStudent() {
        alert('Save Student functionality (static demo)');
        const modal = bootstrap.Modal.getInstance(document.getElementById('studentModal'));
        if (modal) modal.hide();
    }

    editStudent(id) {
        this.showStudentModal(id);
    }

    deleteStudent(id, name) {
        this.showDeleteModal(`Are you sure you want to delete student ${name}?`, () => {
            alert(`Deleting student ${name} (ID: ${id})`);
        });
    }

    // Course Management
    showCourseModal(courseId = null) {
        const modal = new bootstrap.Modal(document.getElementById('courseModal'));
        const form = document.getElementById('courseForm');
        if (form) form.reset();
        
        const courseIdInput = document.getElementById('courseId');
        const modalTitle = document.getElementById('courseModalTitle');
        
        if (courseIdInput) courseIdInput.value = '';
        if (modalTitle) modalTitle.textContent = 'Add Course';
        
        if (courseId) {
            if (modalTitle) modalTitle.textContent = 'Edit Course';
            if (courseIdInput) courseIdInput.value = courseId;
            this.populateCourseForm(courseId);
        }
        modal.show();
    }

    populateCourseForm(courseId) {
        const fields = {
            'courseName': 'Course ' + courseId,
            'courseCode': 'C' + courseId.toString().padStart(3, '0')
        };

        Object.entries(fields).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) element.value = value;
        });
    }

    saveCourse() {
        alert('Save Course functionality (static demo)');
        const modal = bootstrap.Modal.getInstance(document.getElementById('courseModal'));
        if (modal) modal.hide();
    }

    editCourse(id) {
        this.showCourseModal(id);
    }

    deleteCourse(id, name) {
        this.showDeleteModal(`Are you sure you want to delete course ${name}?`, () => {
            alert(`Deleting course ${name} (ID: ${id})`);
        });
    }

    // Exam Results Management
    showExamResultModal(resultId = null) {
        const modal = new bootstrap.Modal(document.getElementById('examResultModal'));
        const form = document.getElementById('examResultForm');
        if (form) form.reset();
        
        const resultIdInput = document.getElementById('examResultId');
        const modalTitle = document.getElementById('examResultModalTitle');
        
        if (resultIdInput) resultIdInput.value = '';
        if (modalTitle) modalTitle.textContent = 'Add Exam Result';
        
        if (resultId) {
            if (modalTitle) modalTitle.textContent = 'Edit Exam Result';
            if (resultIdInput) resultIdInput.value = resultId;
        }
        modal.show();
    }

    saveExamResult() {
        alert('Save Exam Result functionality (static demo)');
        const modal = bootstrap.Modal.getInstance(document.getElementById('examResultModal'));
        if (modal) modal.hide();
    }

    editExamResult(id) {
        this.showExamResultModal(id);
    }

    deleteExamResult(id, name) {
        this.showDeleteModal(`Are you sure you want to delete exam result for ${name}?`, () => {
            alert(`Deleting exam result for ${name} (ID: ${id})`);
        });
    }

    // Program Management
    showProgramModal(programId = null) {
        const modal = new bootstrap.Modal(document.getElementById('programModal'));
        const form = document.getElementById('programForm');
        if (form) form.reset();
        
        const programIdInput = document.getElementById('programId');
        const modalTitle = document.getElementById('programModalTitle');
        
        if (programIdInput) programIdInput.value = '';
        if (modalTitle) modalTitle.textContent = 'Add Program';
        
        if (programId) {
            if (modalTitle) modalTitle.textContent = 'Edit Program';
            if (programIdInput) programIdInput.value = programId;
        }
        modal.show();
    }

    saveProgram() {
        alert('Save Program functionality (static demo)');
        const modal = bootstrap.Modal.getInstance(document.getElementById('programModal'));
        if (modal) modal.hide();
    }

    editProgram(id) {
        this.showProgramModal(id);
    }

    deleteProgram(id, name) {
        this.showDeleteModal(`Are you sure you want to delete program ${name}?`, () => {
            alert(`Deleting program ${name} (ID: ${id})`);
        });
    }

    // Specialty Management
    showSpecialtyModal(specialtyId = null) {
        const modal = new bootstrap.Modal(document.getElementById('specialtyModal'));
        const form = document.getElementById('specialtyForm');
        if (form) form.reset();
        
        const specialtyIdInput = document.getElementById('specialtyId');
        const modalTitle = document.getElementById('specialtyModalTitle');
        
        if (specialtyIdInput) specialtyIdInput.value = '';
        if (modalTitle) modalTitle.textContent = 'Add Specialty';
        
        if (specialtyId) {
            if (modalTitle) modalTitle.textContent = 'Edit Specialty';
            if (specialtyIdInput) specialtyIdInput.value = specialtyId;
        }
        modal.show();
    }

    saveSpecialty() {
        alert('Save Specialty functionality (static demo)');
        const modal = bootstrap.Modal.getInstance(document.getElementById('specialtyModal'));
        if (modal) modal.hide();
    }

    editSpecialty(id) {
        this.showSpecialtyModal(id);
    }

    deleteSpecialty(id, name) {
        this.showDeleteModal(`Are you sure you want to delete specialty ${name}?`, () => {
            alert(`Deleting specialty ${name} (ID: ${id})`);
        });
    }

    // Transcript Management
    showTranscriptModal(transcriptId = null) {
        const modal = new bootstrap.Modal(document.getElementById('transcriptModal'));
        const form = document.getElementById('transcriptForm');
        if (form) form.reset();
        
        const transcriptIdInput = document.getElementById('transcriptId');
        const modalTitle = document.getElementById('transcriptModalTitle');
        
        if (transcriptIdInput) transcriptIdInput.value = '';
        if (modalTitle) modalTitle.textContent = 'Add Transcript Entry';
        
        if (transcriptId) {
            if (modalTitle) modalTitle.textContent = 'Edit Transcript Entry';
            if (transcriptIdInput) transcriptIdInput.value = transcriptId;
        }
        modal.show();
    }

    saveTranscript() {
        alert('Save Transcript functionality (static demo)');
        const modal = bootstrap.Modal.getInstance(document.getElementById('transcriptModal'));
        if (modal) modal.hide();
    }

    editTranscript(id) {
        this.showTranscriptModal(id);
    }

    deleteTranscript(id, name) {
        this.showDeleteModal(`Are you sure you want to delete transcript entry for ${name}?`, () => {
            alert(`Deleting transcript entry for ${name} (ID: ${id})`);
        });
    }

    // Generic Delete Modal
    showDeleteModal(message, onConfirm) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const deleteMessage = document.getElementById('deleteMessage');
        const confirmDelete = document.getElementById('confirmDelete');
        
        if (deleteMessage) deleteMessage.textContent = message;
        
        // Remove existing event listeners
        const newConfirmButton = confirmDelete.cloneNode(true);
        confirmDelete.parentNode.replaceChild(newConfirmButton, confirmDelete);
        
        newConfirmButton.addEventListener('click', () => {
            onConfirm();
            modal.hide();
        });
        
        modal.show();
    }

    confirmDelete() {
        // This will be overridden by showDeleteModal
    }

    // Filter functions (placeholder implementations)
    filterStudents() {
        alert('Filter Students functionality (static demo)');
    }

    filterCourses() {
        alert('Filter Courses functionality (static demo)');
    }

    filterExamResults() {
        alert('Filter Exam Results functionality (static demo)');
    }

    filterPrograms() {
        alert('Filter Programs functionality (static demo)');
    }

    filterSpecialties() {
        alert('Filter Specialties functionality (static demo)');
    }

    filterTranscripts() {
        alert('Filter Transcripts functionality (static demo)');
    }
}

// Global functions for onclick handlers
function showStudentModal(id = null) {
    dashboard.showStudentModal(id);
}

function showCourseModal(id = null) {
    dashboard.showCourseModal(id);
}

function showExamResultModal(id = null) {
    dashboard.showExamResultModal(id);
}

function showProgramModal(id = null) {
    dashboard.showProgramModal(id);
}

function showSpecialtyModal(id = null) {
    dashboard.showSpecialtyModal(id);
}

function showTranscriptModal(id = null) {
    dashboard.showTranscriptModal(id);
}

function editStudent(id) {
    dashboard.editStudent(id);
}

function editCourse(id) {
    dashboard.editCourse(id);
}

function editExamResult(id) {
    dashboard.editExamResult(id);
}

function editProgram(id) {
    dashboard.editProgram(id);
}

function editSpecialty(id) {
    dashboard.editSpecialty(id);
}

function editTranscript(id) {
    dashboard.editTranscript(id);
}

function deleteStudent(id, name) {
    dashboard.deleteStudent(id, name);
}

function deleteCourse(id, name) {
    dashboard.deleteCourse(id, name);
}

function deleteExamResult(id, name) {
    dashboard.deleteExamResult(id, name);
}

function deleteProgram(id, name) {
    dashboard.deleteProgram(id, name);
}

function deleteSpecialty(id, name) {
    dashboard.deleteSpecialty(id, name);
}

function deleteTranscript(id, name) {
    dashboard.deleteTranscript(id, name);
}

function filterStudents() {
    dashboard.filterStudents();
}

function filterCourses() {
    dashboard.filterCourses();
}

function filterExamResults() {
    dashboard.filterExamResults();
}

function filterPrograms() {
    dashboard.filterPrograms();
}

function filterSpecialties() {
    dashboard.filterSpecialties();
}

function filterTranscripts() {
    dashboard.filterTranscripts();
}

// Initialize the dashboard
const dashboard = new StaticAdminDashboard();

