const subtasks = document.querySelectorAll('.subtasks input[type="checkbox"]');
const progressBar = document.querySelector('.progress-bar-fill');
const progressText = document.querySelector('.progress p');

subtasks.forEach(subtask => {
    subtask.addEventListener('change', () => {
        const completed = Array.from(subtasks).filter(task => task.checked).length;
        const total = subtasks.length;
        const progress = (completed / total) * 100;

        progressBar.style.width = `${progress}%`;
        progressText.textContent = `${Math.round(progress)}% Completed`;
    });
});
