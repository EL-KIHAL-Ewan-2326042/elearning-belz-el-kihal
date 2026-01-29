export default function ProgressBar({ progress }) {
    return (
        <div className="w-full bg-gray-200 rounded-full h-2.5">
            <div
                className="hero-gradient h-2.5 rounded-full transition-all duration-500"
                style={{ width: `${progress}%` }}
            />
        </div>
    );
}
