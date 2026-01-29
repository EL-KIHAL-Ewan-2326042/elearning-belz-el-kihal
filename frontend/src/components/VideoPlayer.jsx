import ReactPlayer from 'react-player';

export default function VideoPlayer({ video }) {
    const videoUrl = video.url || (video.fileName ? `/uploads/videos/${video.fileName}` : null);

    if (!videoUrl) {
        return (
            <div className="aspect-video bg-gray-800 flex items-center justify-center">
                <span className="text-white text-lg">Vidéo non disponible</span>
            </div>
        );
    }

    return (
        <div className="aspect-video bg-black">
            <ReactPlayer
                url={videoUrl}
                width="100%"
                height="100%"
                controls
                playing={false}
                config={{
                    file: {
                        attributes: {
                            controlsList: 'nodownload',
                        },
                    },
                }}
            />
        </div>
    );
}
