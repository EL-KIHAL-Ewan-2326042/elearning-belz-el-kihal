export default function ScoreBadge({ score, maxScore }) {
    const percentage = (score / maxScore) * 100;

    let colorClass = '';
    if (percentage >= 80) colorClass = 'score-excellent';
    else if (percentage >= 60) colorClass = 'score-good';
    else if (percentage >= 40) colorClass = 'score-average';
    else colorClass = 'score-poor';

    return (
        <span className={`score-badge ${colorClass}`}>
            {score}/{maxScore}
        </span>
    );
}
