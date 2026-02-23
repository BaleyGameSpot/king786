package com.utils;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.drawable.Drawable;
import android.net.Uri;
import android.widget.ImageView;

import androidx.annotation.Nullable;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.DataSource;
import com.bumptech.glide.load.engine.GlideException;
import com.bumptech.glide.load.resource.gif.GifDrawable;
import com.bumptech.glide.request.RequestListener;
import com.bumptech.glide.request.RequestOptions;
import com.bumptech.glide.request.target.Target;
import com.buddyverse.providers.R;

import java.io.File;
import java.net.URL;

public class LoadImageGlide {

    public enum GlideRequestBuilder {BITMAP, FILE, GIF, DRAWABLE}

    public enum GlideIMAGESUPPORT {BITMAP, DRAWABLE, STRING, URI, FILE, INTEGER, URL}

    private static LoadImageGlide instance;

    private Context mContext;
    TypeBuilder builder;
    private ImageView imageLoaderView;
    private GlideListener glideListener;

    private RequestOptions requestOptions;
    private GlideRequestBuilder glideRequestBuilder;
    private int placeholderImagePath = -1;
    private int errorImagePath = -1;


    public LoadImageGlide(builder builder) {
        this.imageLoaderView = builder.imageLoaderView;
        this.glideListener = builder.glideListener;
        this.requestOptions = builder.requestOptions;
        this.glideRequestBuilder = builder.glideRequestBuilder;
        this.mContext = builder.mContext;
        this.placeholderImagePath = builder.placeholderImagePath;
        this.errorImagePath = builder.errorImagePath;
        this.builder = builder.builder;

        loadImage();
    }

    public static class builder {
        private ImageView imageLoaderView;
        private GlideListener glideListener;
        private RequestOptions requestOptions;
        private GlideRequestBuilder glideRequestBuilder;
        private Context mContext;
        private int placeholderImagePath = -1;
        private int errorImagePath = -1;
        TypeBuilder builder;

        public builder(Context mContext, TypeBuilder builder, ImageView imageLoaderView) {
            this.builder = builder;
            this.imageLoaderView = imageLoaderView;
            this.mContext = mContext;
        }

        public builder setBuilder(TypeBuilder builder) {
            this.builder = builder;
            return this;
        }

        public builder setmContext(Context mContext) {
            this.mContext = mContext;
            return this;
        }

        public builder setPlaceholderImagePath(int placeholderImagePath) {
            this.placeholderImagePath = placeholderImagePath;
            return this;
        }

        public builder setErrorImagePath(int errorImagePath) {
            this.errorImagePath = errorImagePath;
            return this;
        }

        public builder setImageLoaderView(ImageView imageLoaderView) {
            this.imageLoaderView = imageLoaderView;
            return this;
        }

        public builder setGlideListener(GlideListener glideListener) {
            this.glideListener = glideListener;
            return this;
        }

        public builder setRequestOptions(RequestOptions requestOptions) {
            this.requestOptions = requestOptions;
            return this;
        }

        public builder setGlideRequestBuilder(GlideRequestBuilder glideRequestBuilder) {
            this.glideRequestBuilder = glideRequestBuilder;
            return this;
        }

        public LoadImageGlide build() {
            return new LoadImageGlide(this);
        }
    }

    public String getURLForResource(int resourceId) {
        //use BuildConfig.APPLICATION_ID instead of R.class.getPackage().getName() if both are not same
        return Uri.parse("android.resource://" + R.class.getPackage().getName() + "/" + resourceId).toString();
    }

    private void loadImage() {
        Object imagePath = "123";
        if (builder.imageType == GlideIMAGESUPPORT.BITMAP) {
            imagePath = builder.bitmapFile;
        } else if (builder.imageType == GlideIMAGESUPPORT.DRAWABLE) {
            imagePath = builder.drawableFile;
        } else if (builder.imageType == GlideIMAGESUPPORT.STRING) {
            imagePath = builder.stringPath;
        } else if (builder.imageType == GlideIMAGESUPPORT.URI) {
            imagePath = builder.uriPath;
        } else if (builder.imageType == GlideIMAGESUPPORT.FILE) {
            imagePath = builder.fileName;
        } else if (builder.imageType == GlideIMAGESUPPORT.INTEGER) {
            imagePath = builder.integerFile;
        } else if (builder.imageType == GlideIMAGESUPPORT.URL) {
            imagePath = builder.urlPath.toString();

        }



        if (this.placeholderImagePath != -1 && this.errorImagePath != -1) {
            this.requestOptions = new RequestOptions().placeholder(placeholderImagePath).error(errorImagePath);
        } else if (this.placeholderImagePath != -1) {
            this.requestOptions = new RequestOptions().placeholder(placeholderImagePath);
        }
        if (requestOptions != null && glideListener != null) {
            if (glideRequestBuilder == GlideRequestBuilder.BITMAP) {
                Glide.with(mContext).asBitmap().load(imagePath).apply(requestOptions).listener(GlideBitmapListener).into(imageLoaderView);
            } else if (glideRequestBuilder == GlideRequestBuilder.FILE) {
                Glide.with(mContext).asFile().load(imagePath).apply(requestOptions).listener(GlideFileListener).into(imageLoaderView);
            } else if (glideRequestBuilder == GlideRequestBuilder.GIF) {
                Glide.with(mContext).asGif().load(imagePath).apply(requestOptions).listener(GlideGIFListener).into(imageLoaderView);
            } else {
                Glide.with(mContext).load(imagePath).apply(requestOptions).listener(GlideDrawableListener).into(imageLoaderView);
            }

        } else if (glideListener != null) {
            if (glideRequestBuilder == GlideRequestBuilder.BITMAP) {
                Glide.with(mContext).asBitmap().load(imagePath).listener(GlideBitmapListener).into(imageLoaderView);
            } else if (glideRequestBuilder == GlideRequestBuilder.FILE) {
                Glide.with(mContext).asFile().load(imagePath).listener(GlideFileListener).into(imageLoaderView);
            } else if (glideRequestBuilder == GlideRequestBuilder.GIF) {
                Glide.with(mContext).asGif().load(imagePath).listener(GlideGIFListener).into(imageLoaderView);
            } else {
                Glide.with(mContext).load(imagePath).listener(GlideDrawableListener).into(imageLoaderView);
            }

        } else if (requestOptions != null) {

            if (glideRequestBuilder == GlideRequestBuilder.BITMAP) {
                Glide.with(mContext).asBitmap().load(imagePath).apply(requestOptions).into(imageLoaderView);
            } else if (glideRequestBuilder == GlideRequestBuilder.FILE) {
                Glide.with(mContext).asFile().load(imagePath).apply(requestOptions).into(imageLoaderView);
            } else if (glideRequestBuilder == GlideRequestBuilder.GIF) {
                Glide.with(mContext).asGif().load(imagePath).apply(requestOptions).into(imageLoaderView);
            } else {
                Glide.with(mContext).load(imagePath).apply(requestOptions).into(imageLoaderView);
            }
        } else {
            if (glideRequestBuilder == GlideRequestBuilder.BITMAP) {
                Glide.with(mContext).asBitmap().load(imagePath).into(imageLoaderView);
            } else if (glideRequestBuilder == GlideRequestBuilder.FILE) {
                Glide.with(mContext).asFile().load(imagePath).into(imageLoaderView);
            } else if (glideRequestBuilder == GlideRequestBuilder.GIF) {
                Glide.with(mContext).asGif().load(imagePath).into(imageLoaderView);
            } else {
                Glide.with(mContext).load(imagePath).into(imageLoaderView);
            }
        }


    }



    public interface GlideListener {
        void onLoadFailed();

        void onResourceReady();
    }

    private static class TypeBuilder {
        private Enum<GlideIMAGESUPPORT> imageType;
        private Bitmap bitmapFile;
        private Drawable drawableFile;
        private String stringPath;
        private Uri uriPath;
        private File fileName;
        private Integer integerFile;
        private URL urlPath;

        public TypeBuilder(Bitmap bitmapFile) {
            this.imageType = GlideIMAGESUPPORT.BITMAP;
            this.bitmapFile = bitmapFile;
        }

        public TypeBuilder(Drawable drawableFile) {
            this.imageType = GlideIMAGESUPPORT.DRAWABLE;
            this.drawableFile = drawableFile;
        }

        public TypeBuilder(String stringPath) {
            this.imageType = GlideIMAGESUPPORT.STRING;
            this.stringPath = stringPath;
        }

        public TypeBuilder(Uri uriPath) {
            this.imageType = GlideIMAGESUPPORT.URI;
            this.uriPath = uriPath;
        }

        public TypeBuilder(File fileName) {
            this.imageType = GlideIMAGESUPPORT.FILE;
            this.fileName = fileName;
        }

        public TypeBuilder(Integer integerFile) {
            this.imageType = GlideIMAGESUPPORT.INTEGER;
            this.integerFile = integerFile;
        }

        public TypeBuilder(URL urlPath) {
            this.imageType = GlideIMAGESUPPORT.URL;
            this.urlPath = urlPath;
        }


    }

    public static TypeBuilder bind(Bitmap bitmapFile) {
        return new TypeBuilder(bitmapFile);
    }

    public static TypeBuilder bind(Drawable drawableFile) {
        return new TypeBuilder(drawableFile);
    }

    public static TypeBuilder bind(String stringPath) {
        return new TypeBuilder(stringPath);
    }

    public static TypeBuilder bind(Uri uriPath) {
        return new TypeBuilder(uriPath);
    }

    public static TypeBuilder bind(File fileName) {
        return new TypeBuilder(fileName);
    }

    public static TypeBuilder bind(Integer integerFile) {
        return new TypeBuilder(integerFile);
    }

    public static TypeBuilder bind(URL urlPath) {
        return new TypeBuilder(urlPath);
    }

    RequestListener<Bitmap> GlideBitmapListener = new RequestListener<Bitmap>() {
        @Override
        public boolean onLoadFailed(@Nullable GlideException e, Object model, Target<Bitmap> target, boolean isFirstResource) {
            glideListener.onLoadFailed();
            return false;
        }

        @Override
        public boolean onResourceReady(Bitmap resource, Object model, Target<Bitmap> target, DataSource dataSource, boolean isFirstResource) {
            glideListener.onResourceReady();
            return false;
        }
    };

    RequestListener<File> GlideFileListener = new RequestListener<File>() {
        @Override
        public boolean onLoadFailed(@Nullable GlideException e, Object model, Target<File> target, boolean isFirstResource) {
            glideListener.onLoadFailed();
            return false;
        }

        @Override
        public boolean onResourceReady(File resource, Object model, Target<File> target, DataSource dataSource, boolean isFirstResource) {
            glideListener.onResourceReady();
            return false;
        }
    };

    RequestListener<GifDrawable> GlideGIFListener = new RequestListener<GifDrawable>() {
        @Override
        public boolean onLoadFailed(@Nullable GlideException e, Object model, Target<GifDrawable> target, boolean isFirstResource) {
            glideListener.onLoadFailed();
            return false;
        }

        @Override
        public boolean onResourceReady(GifDrawable resource, Object model, Target<GifDrawable> target, DataSource dataSource, boolean isFirstResource) {
            glideListener.onResourceReady();
            return false;
        }
    };

    RequestListener<Drawable> GlideDrawableListener = new RequestListener<Drawable>() {
        @Override
        public boolean onLoadFailed(@Nullable GlideException e, Object model, Target<Drawable> target, boolean isFirstResource) {
            glideListener.onLoadFailed();
            return false;
        }

        @Override
        public boolean onResourceReady(Drawable resource, Object model, Target<Drawable> target, DataSource dataSource, boolean isFirstResource) {
            glideListener.onResourceReady();
            return false;
        }
    };


}


