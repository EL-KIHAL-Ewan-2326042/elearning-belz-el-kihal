import { Link } from 'react-router-dom';

export default function CourseCard({ course }) {
    return (
        <div className="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl hover:-translate-y-1 transition duration-300">
            <div className="h-40 video-gradient flex items-center justify-center">
                <span className="text-6xl">📚</span>
            </div>

            <div className="p-6">
                <h3 className="text-lg font-bold text-gray-800 mb-2 line-clamp-2">
                    {course.title}
                </h3>
                <p className="text-gray-600 text-sm mb-4 line-clamp-2">
                    {course.description}
                </p>

                <div className="flex items-center justify-between">
                    <span className="text-sm text-gray-500">
                        👨‍🏫 {course.teacher?.firstName || 'Prof.'} {course.teacher?.lastName || ''}
                    </span>
                    <Link
                        to={`/course/${course.id}`}
                        className="px-4 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-secondary transition no-underline"
                    >
                        Voir le cours
                    </Link>
                </div>
            </div>
        </div>
    );
}
